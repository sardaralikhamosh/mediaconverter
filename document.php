<?php
session_start();

// Configuration
$uploadDir = __DIR__ . '/uploads/';
$convertedDir = __DIR__ . '/converted/';
$allowedMimeTypes = [
    'application/pdf', 
    'text/plain',
    'text/csv',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

// Create directories if they don't exist
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
if (!file_exists($convertedDir)) mkdir($convertedDir, 0755, true);

// Process file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['docFile'])) {
    $file = $_FILES['docFile'];
    $targetFormat = $_POST['target_format'] ?? 'pdf';
    
    try {
        // Validate input
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        // Verify file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception('Invalid file type. Supported formats: PDF, DOC, DOCX, TXT, CSV');
        }

        // Generate safe filename
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFilename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $originalName);
        $newFilename = uniqid() . '_' . $safeFilename . '.' . $extension;
        $uploadPath = $uploadDir . $newFilename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Redirect to conversion script
        header("Location: convert_documents.php?" . http_build_query([
            'file' => $newFilename,
            'target' => $targetFormat
        ]));
        exit;

    } catch (Exception $e) {
        $uploadError = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Converter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <style>
        .converter-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .file-preview {
            border: 2px dashed #007bff;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .alert {
            margin-top: 1.5rem;
        }
        .btn-convert {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .btn-convert:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="converter-container">
            <h2 class="text-center mb-4">Document Converter</h2>
            <p class="text-center text-muted mb-4">
                Convert between PDF, DOCX, TXT, and CSV formats
            </p>

            <form class="file-upload-form" method="post" enctype="multipart/form-data" action="document.php">
                <div class="file-preview" id="filePreview">
                    <span class="text-muted">No file selected</span>
                </div>

                <div class="form-group">
                    <input type="file" name="docFile" class="form-control-file" 
                           id="fileInput" required accept=".pdf,.doc,.docx,.txt,.csv">
                </div>

                <div class="form-group">
                    <select name="target_format" class="form-control" id="targetFormat" required>
                        <option value="">Convert to...</option>
                        <option value="pdf">PDF Document</option>
                        <option value="docx">Word Document (DOCX)</option>
                        <option value="txt">Plain Text (TXT)</option>
                        <option value="csv">CSV File</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-convert btn-block">
                    Convert Now
                </button>
            </form>

            <?php if (!empty($uploadError)): ?>
                <div class="alert alert-danger mt-4">
                    <?= htmlspecialchars($uploadError) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // File input preview handler
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            
            if (file) {
                preview.innerHTML = `
                    <strong>${file.name}</strong><br>
                    <small class="text-muted">
                        ${(file.size/1024).toFixed(2)} KB • 
                        ${file.type.replace('application/', '').toUpperCase()}
                    </small>
                `;
            } else {
                preview.innerHTML = '<span class="text-muted">No file selected</span>';
            }
        });

        // Form validation
        document.getElementById('conversionForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('fileInput');
            const formatSelect = document.getElementById('targetFormat');
            
            if (!fileInput.files.length || !formatSelect.value) {
                e.preventDefault();
                alert('Please select a file and conversion format');
            }
        });
    </script>
</body>
</html>