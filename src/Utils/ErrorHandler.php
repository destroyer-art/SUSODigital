<?php
namespace App\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ErrorHandler {
    private $logger;
    
    public function __construct() {
        $this->logger = new Logger('performance-analyzer');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/error.log', Logger::ERROR));
    }
    
    public function handleError($error) {
        $this->logger->error($error->getMessage(), [
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString()
        ]);
        
        if (getenv('APP_DEBUG') === 'true') {
            return [
                'success' => false,
                'error' => $error->getMessage(),
                'details' => [
                    'file' => $error->getFile(),
                    'line' => $error->getLine()
                ]
            ];
        }
        
        return [
            'success' => false,
            'error' => 'An unexpected error occurred'
        ];
    }
} 