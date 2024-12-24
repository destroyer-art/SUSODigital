<?php
require 'vendor/autoload.php';

$config = require 'config/config.php';

$uploadController = new App\Controllers\UploadController($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo $uploadController->handleUpload();
    exit;
}

require 'templates/upload.html'; 