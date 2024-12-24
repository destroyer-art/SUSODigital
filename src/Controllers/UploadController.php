<?php
namespace App\Controllers;

use App\Services\OpenAIService;
use App\Services\ImageProcessingService;

class UploadController {
    private $config;
    private $openAIService;
    private $imageProcessor;
    
    public function __construct($config) {
        $this->config = $config;
        $this->openAIService = new OpenAIService($config['openai_key']);
        $this->imageProcessor = new ImageProcessingService($config);
    }
    
    public function handleUpload() {
        try {
            if (!isset($_FILES['files'])) {
                throw new \Exception('No files were uploaded');
            }

            $uploadedFiles = $this->validateAndUploadFiles($_FILES['files']);
            if (empty($uploadedFiles)) {
                throw new \Exception('No valid files were uploaded');
            }

            $analysisResults = $this->analyzeImages($uploadedFiles);
            $report = $this->generateReport($analysisResults);
            
            return json_encode([
                'success' => true,
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function analyzeImages($uploadedFiles) {
        $results = [];
        
        foreach ($uploadedFiles as $file) {
            // Process the image for better analysis
            $processedImage = $this->imageProcessor->processImage($file);
            
            // Analyze the processed image using OpenAI
            $analysis = $this->openAIService->analyzePerformanceGraphs([$processedImage]);
            
            $results[] = [
                'file' => basename($file),
                'analysis' => $analysis[0]['choices'][0]['message']['content'] ?? 'No analysis available',
                'metrics' => $this->extractMetrics($analysis)
            ];
        }
        
        return $results;
    }

    private function generateReport($analysisResults) {
        $html = '<div class="report">';
        $html .= '<h2>Performance Analysis Report</h2>';
        
        foreach ($analysisResults as $result) {
            $html .= '<div class="graph-analysis">';
            $html .= '<h3>Analysis for ' . htmlspecialchars($result['file']) . '</h3>';
            $html .= '<p>' . nl2br(htmlspecialchars($result['analysis'])) . '</p>';
            
            if (!empty($result['metrics'])) {
                $html .= '<div class="metrics">';
                foreach ($result['metrics'] as $metric => $value) {
                    $html .= '<div class="metric-item">';
                    $html .= '<span class="metric-label">' . htmlspecialchars($metric) . ':</span>';
                    $html .= '<span class="metric-value">' . htmlspecialchars($value) . '</span>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    private function extractMetrics($analysis) {
        // Extract any numerical metrics from the analysis
        $metrics = [];
        
        if (isset($analysis[0]['choices'][0]['message']['content'])) {
            $content = $analysis[0]['choices'][0]['message']['content'];
            
            // Look for patterns like "metric: value" or "metric = value"
            preg_match_all('/([a-zA-Z\s]+)[:=]\s*([\d.]+)%?/i', $content, $matches);
            
            if (!empty($matches[1])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $metric = trim($matches[1][$i]);
                    $value = trim($matches[2][$i]);
                    $metrics[$metric] = $value;
                }
            }
        }
        
        return $metrics;
    }

    private function validateAndUploadFiles($files) {
        $uploadedFiles = [];
        
        foreach ($files['tmp_name'] as $key => $tmp_name) {
            $file_type = $files['type'][$key];
            $file_size = $files['size'][$key];
            
            if (!in_array($file_type, $this->config['allowed_types'])) {
                continue;
            }
            
            if ($file_size > $this->config['max_file_size']) {
                continue;
            }
            
            $filename = uniqid() . '_' . $files['name'][$key];
            $destination = $this->config['upload_dir'] . $filename;
            
            if (move_uploaded_file($tmp_name, $destination)) {
                $uploadedFiles[] = $destination;
            }
        }
        
        return $uploadedFiles;
    }
} 