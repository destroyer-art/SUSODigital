<?php
namespace App\Services;

class OpenAIService {
    private $api_key;
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
        
        if (!extension_loaded('curl')) {
            throw new \Exception('cURL extension is not installed');
        }
    }
    
    public function analyzePerformanceGraphs($images) {
        $analysis = [];
        
        foreach ($images as $image) {
            try {
                $base64Image = base64_encode(file_get_contents($image));
                
                $response = $this->makeOpenAIRequest([
                    'model' => 'gpt-3.5-turbo-vision-preview',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Analyze this performance graph. Please provide: 1) Key trends 2) Notable metrics 3) Any anomalies or patterns'
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:image/jpeg;base64,{$base64Image}"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'max_tokens' => 300
                ]);
                
                $analysis[] = json_decode($response, true);
            } catch (\Exception $e) {
                $analysis[] = [
                    'error' => $e->getMessage(),
                    'choices' => [[
                        'message' => [
                            'content' => 'Failed to analyze image: ' . $e->getMessage()
                        ]
                    ]]
                ];
            }
        }
        
        return $analysis;
    }
    
    private function makeOpenAIRequest($data) {
        $ch = \curl_init('https://api.openai.com/v1/chat/completions');
        
        if ($ch === false) {
            throw new \Exception('Failed to initialize cURL');
        }
        
        \curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = \curl_exec($ch);
        $error = \curl_error($ch);
        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        \curl_close($ch);
        
        if ($response === false) {
            throw new \Exception('cURL error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            throw new \Exception('OpenAI API error: ' . 
                ($errorData['error']['message'] ?? 'Unknown error') . 
                ' (HTTP code: ' . $httpCode . ')'
            );
        }
        
        return $response;
    }
}