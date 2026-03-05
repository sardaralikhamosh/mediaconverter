<?php
$uploadError = '';
$downloadLink = '';

// Create directories if they don't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}
if (!file_exists('converted')) {
    mkdir('converted', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pngFile'])) {
    $file = $_FILES['pngFile'];
    
    // Validate file type and extension
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if ($mime == 'image/png' && preg_match('/\.png$/i', $file['name'])) {
        $uploadDir = 'uploads/';
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._\-]/', '', basename($file['name']));
        $pngPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $pngPath)) {
            $pdfPath = 'converted/' . pathinfo($filename, PATHINFO_FILENAME) . '.pdf';

            try {
                $imagick = new Imagick();
                $imagick->readImage($pngPath);
                $imagick->setImageFormat('pdf');
                $imagick->writeImage($pdfPath);
                $imagick->clear();
                
                $downloadLink = $pdfPath;
            } catch (Exception $e) {
                $uploadError = 'Error converting PNG to PDF: ' . $e->getMessage();
                @unlink($pngPath); // Clean up uploaded file
            }
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Invalid file format. Please upload a PNG file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert PNG to PDF</title> <!-- Fixed title -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom.css">
</head>
<body>
    <?php include 'header.php'; ?>

<div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
        <div class="content" style="width: 100%; max-width: 600px;">
                <h2 class="text-center">Convert PNG to PDF</h2>
                <p class="text-center">Upload a PNG file to convert it to PDF format.</p>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="pngFile">Choose PNG file:</label>
                        <input type="file" name="pngFile" class="form-control" id="pngFile" accept=".png" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Convert to PDF</button>
                </form>

                <?php if ($uploadError): ?>
                    <div class="alert alert-danger mt-20">
                        <?= htmlspecialchars($uploadError) ?>
                    </div>
                <?php endif; ?>

                <?php if ($downloadLink): ?>
                    <div class="alert alert-success mt-20">
                        <p>Conversion successful! Download your PDF file:</p>
                        <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>
                            Download PDF
                        </a>
                        <a href="png-to-pdf.php" class="btn btn-default btn-block mt-10">
                            Convert Another File
                        </a>
                    </div>
                <?php endif; ?>
            </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
</body>
</html>