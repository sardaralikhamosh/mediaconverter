<?php
session_start();

// Check if Imagick is available
if (!extension_loaded('imagick')) {
    die('Imagick extension is not available.');
}

$uploadError = '';
$downloadLink = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $uploadError = 'Invalid CSRF token.';
    } elseif (isset($_FILES['heicFile'])) {
        $file = $_FILES['heicFile'];

        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $uploadError = 'File size too large (max 10MB)';
        } else {
            try {
                // Validate HEIC file using Imagick
                $imagick = new Imagick($file['tmp_name']);
                $format = $imagick->getImageFormat();
                if (!in_array(strtoupper($format), ['HEIC', 'HEIF'])) {
                    throw new Exception('Invalid HEIC file');
                }
                $imagick->clear();

                // Create directories if they don't exist
                $uploadDir = 'heic_uploads/';
                $convertedDir = 'converted_pdfs/';
                
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!file_exists($convertedDir)) mkdir($convertedDir, 0755, true);

                // Generate unique filenames
                $filename = uniqid() . '.heic';
                $heicPath = $uploadDir . $filename;
                $pdfPath = $convertedDir . pathinfo($filename, PATHINFO_FILENAME) . '.pdf';

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $heicPath)) {
                    // Convert HEIC to PDF
                    $imagick = new Imagick($heicPath);
                    $imagick->setImageFormat('pdf');
                    $imagick->writeImage($pdfPath);
                    $imagick->clear();
                    $imagick->destroy();

                    // Delete original HEIC file
                    if (file_exists($heicPath)) {
                        unlink($heicPath);
                    }

                    $downloadLink = $pdfPath;
                } else {
                    $uploadError = 'Error uploading file.';
                }
            } catch (Exception $e) {
                error_log('Conversion error: ' . $e->getMessage());
                $uploadError = 'Invalid HEIC file or conversion error.';
                
                // Clean up any temporary files
                if (isset($heicPath) && file_exists($heicPath)) {
                    unlink($heicPath);
                }
            }
        }
    } else {
        $uploadError = 'Please select a file to upload.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert HEIC to PDF</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css">
    <style>
        .container-wrapper {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .conversion-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="container-wrapper">
            <div class="col-md-8 col-md-offset-2">
                <div class="conversion-box">
                    <h2 class="text-center">HEIC to PDF Converter</h2>
                    <p class="text-center text-muted">Convert your HEIC/HEIF images to PDF format</p>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="form-group">
                            <label for="heicFile">Select HEIC/HEIF File:</label>
                            <input type="file" name="heicFile" class="form-control" 
                                   id="heicFile" accept=".heic,.heif" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="glyphicon glyphicon-convert"></span> Convert to PDF
                        </button>
                    </form>

                    <?php if ($uploadError): ?>
                        <div class="alert alert-danger mt-20">
                            <?= htmlspecialchars($uploadError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($downloadLink): ?>
                        <div class="alert alert-success mt-20">
                            <h4>Conversion Successful!</h4>
                            <p>Your PDF file is ready:</p>
                            <a href="<?= htmlspecialchars($downloadLink) ?>" 
                               class="btn btn-success btn-block" download>
                               Download PDF
                            </a>
                            <div class="mt-10">
                                <a href="heic-to-pdf.php" class="btn btn-default btn-block">
                                    Convert Another File
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
</body>
</html>