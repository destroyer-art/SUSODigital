# Performance Graph Analysis Tool

A web-based application that allows users to upload and analyze performance graphs through drag-and-drop functionality and AI-powered analysis.

## Features

- 📊 Drag-and-drop file upload interface
- 🖼️ Image preview functionality
- ✨ Real-time file validation
- 🤖 AI-powered graph analysis
- 📱 Responsive design
- 🔒 Secure file handling

## Requirements

- PHP 7.4 or higher
- Composer
- OpenAI API key
- Modern web browser with JavaScript enabled
- Write permissions for upload directory

## Installation

1. Clone the repository: 
2. Install dependencies:
```
composer install
```
3. Copy the configuration file:
4. Update the configuration file with your settings:
- Set your OpenAI API key
- Configure upload directory paths
- Adjust file size limits if needed
5. Set proper permissions:


## Usage

1. Start your PHP server:
```
php -S localhost:8000
```

2. Access the application through your web browser:
3. Upload files by either:
   - Dragging and dropping files onto the upload area
   - Clicking the "Select Files" button
   - Using the file input dialog

4. Review the previews of uploaded files
5. Submit for analysis
6. View the generated report

## Supported File Types

- PNG (.png)
- JPEG (.jpg, .jpeg)
- GIF (.gif)

## File Restrictions

- Maximum file size: 5MB per file
- File type must be image
- Files must be valid performance graphs
