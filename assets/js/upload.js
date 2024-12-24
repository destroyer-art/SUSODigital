document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const preview = document.getElementById('preview');
    let selectedFiles = new FormData();

    // Debug
    console.log('Initializing drag and drop...');
    console.log('DropZone:', dropZone);

    // Prevent default drag behaviors on document
    document.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
    });

    document.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
    });

    // Handle drag events on dropZone
    dropZone.addEventListener('dragenter', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('File entered dropzone');
        dropZone.classList.add('highlight');
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('highlight');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('File left dropzone');
        dropZone.classList.remove('highlight');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('File dropped');
        dropZone.classList.remove('highlight');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle file input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    // Handle file selection
    function handleFiles(files) {
        console.log('Handling files:', files.length);
        
        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert('Please upload only image files');
                return;
            }

            // Add to FormData
            selectedFiles.append('files[]', file);

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';

                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-file';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = () => {
                    // Remove from FormData and preview
                    const newFormData = new FormData();
                    for (let [key, value] of selectedFiles.entries()) {
                        if (value.name !== file.name) {
                            newFormData.append(key, value);
                        }
                    }
                    selectedFiles = newFormData;
                    previewItem.remove();
                };

                previewItem.appendChild(img);
                previewItem.appendChild(removeBtn);
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    }

    // Handle form submission
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Form submitted');

        if ([...selectedFiles.getAll('files[]')].length === 0) {
            alert('Please select at least one file');
            return;
        }

        try {
            const response = await fetch('index.php', {
                method: 'POST',
                body: selectedFiles
            });

            const result = await response.json();
            console.log('Upload result:', result);

            if (result.success) {
                displayReport(result.report);
                // Clear the form after successful upload
                preview.innerHTML = '';
                selectedFiles = new FormData();
            } else {
                alert('Error: ' + (result.error || 'Unknown error occurred'));
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Upload failed: ' + error.message);
        }
    });

    function displayReport(report) {
        const modal = document.createElement('div');
        modal.className = 'report-modal';
        modal.innerHTML = `
            <div class="report-content">
                <div class="report-header">
                    <h2>Performance Analysis Report</h2>
                    <button class="close-report" onclick="this.closest('.report-modal').remove()">×</button>
                </div>
                <div class="report-body">
                    ${report}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Make the entire dropZone clickable
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });
}); 