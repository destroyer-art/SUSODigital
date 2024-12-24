<?php
namespace App\Services;

class ReportGeneratorService {
    private $openAIService;
    
    public function __construct($openAIService) {
        $this->openAIService = $openAIService;
    }
    
    public function generateReport($analysisResults) {
        $report = [
            'summary' => $this->generateSummary($analysisResults),
            'details' => $this->generateDetails($analysisResults),
            'recommendations' => $this->generateRecommendations($analysisResults),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $this->formatReport($report);
    }
    
    private function generateSummary($analysisResults) {
        // Combine all analysis results into a coherent summary
        $prompt = "Generate a summary of the following performance analysis:\n\n";
        foreach ($analysisResults as $result) {
            $prompt .= $result['analysis'] . "\n";
        }
        
        return $this->openAIService->generateText($prompt);
    }
    
    private function generateDetails($analysisResults) {
        $details = [];
        foreach ($analysisResults as $index => $result) {
            $details[] = [
                'graph_number' => $index + 1,
                'analysis' => $result['analysis'],
                'metrics' => $result['metrics'] ?? []
            ];
        }
        return $details;
    }
    
    private function generateRecommendations($analysisResults) {
        $prompt = "Based on the following analysis, provide actionable recommendations:\n\n";
        foreach ($analysisResults as $result) {
            $prompt .= $result['analysis'] . "\n";
        }
        
        return $this->openAIService->generateText($prompt);
    }
    
    private function formatReport($report) {
        $html = '<div class="report">';
        $html .= '<h2>Performance Analysis Report</h2>';
        $html .= '<div class="report-timestamp">Generated on: ' . $report['timestamp'] . '</div>';
        
        $html .= '<div class="report-section">';
        $html .= '<h3>Executive Summary</h3>';
        $html .= '<p>' . $report['summary'] . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="report-section">';
        $html .= '<h3>Detailed Analysis</h3>';
        foreach ($report['details'] as $detail) {
            $html .= '<div class="graph-analysis">';
            $html .= '<h4>Graph ' . $detail['graph_number'] . '</h4>';
            $html .= '<p>' . $detail['analysis'] . '</p>';
            if (!empty($detail['metrics'])) {
                $html .= '<ul class="metrics">';
                foreach ($detail['metrics'] as $metric => $value) {
                    $html .= '<li><strong>' . $metric . ':</strong> ' . $value . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="report-section">';
        $html .= '<h3>Recommendations</h3>';
        $html .= '<p>' . $report['recommendations'] . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
} 