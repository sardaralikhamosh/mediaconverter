<?php
// document.php - Updated with proper form handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['docFile'])) {
    // Temporary error message handling
    $uploadError = '';
    $downloadLink = '';

    // Basic file validation
    $file = $_FILES['docFile'];
    $allowedTypes = ['application/pdf', 'text/plain', 'text/csv', 
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    if (in_array($file['type'], $allowedTypes)) {
        $uploadDir = 'uploads/';
        $convertedDir = 'converted/';
        
        // Create directories if they don't exist
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
        if (!file_exists($convertedDir)) mkdir($convertedDir, 0755, true);

        // Generate safe filename
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._\-]/', '', basename($file['name']));
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Process conversion
            header("Location: convert_documents.php?file=" . urlencode($uploadPath));
            exit;
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Invalid file type. Please upload PDF, DOC, DOCX, TXT, or CSV files.';
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
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .preview-area {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container converter-container">
        <h2 class="text-center mb-4">Document Converter</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="preview-area">
                <div class="form-group">
                    <input type="file" name="docFile" class="form-control-file" id="fileInput" required>
                </div>
                
                <div class="form-group">
                    <select name="target_format" class="form-control" required>
                        <option value="">Convert to...</option>
                        <option value="pdf">PDF</option>
                        <option value="docx">DOCX</option>
                        <option value="txt">TXT</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Convert File</button>
        </form>

        <?php if (!empty($uploadError)): ?>
            <div class="alert alert-danger mt-4"><?= htmlspecialchars($uploadError) ?></div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // File input preview script
        document.getElementById('fileInput').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            document.querySelector('.preview-area').innerHTML = `
                <p>Selected file: <strong>${fileName}</strong></p>
                ${document.querySelector('.preview-area').innerHTML}
            `;
        });
    </script>
</body>
</html>