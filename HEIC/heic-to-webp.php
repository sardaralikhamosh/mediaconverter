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

                // Set directory paths
                $baseDir = __DIR__;
                $uploadDir = $baseDir . '/heic_uploads/';
                $convertedDir = $baseDir . '/converted_webp/';
                
                // Create directories if missing
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!file_exists($convertedDir)) mkdir($convertedDir, 0755, true);

                // Generate unique filenames
                $filename = uniqid() . '.heic';
                $heicPath = $uploadDir . $filename;
                $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                $webpPath = $convertedDir . $webpFilename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $heicPath)) {
                    // Convert HEIC to WebP
                    $imagick = new Imagick($heicPath);
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(80);
                    $imagick->setOption('webp:method', '6');
                    $imagick->writeImage($webpPath);
                    $imagick->clear();
                    $imagick->destroy();

                    // Cleanup files
                    if (file_exists($heicPath)) unlink($heicPath);

                    // Set web path for download
                    $downloadLink = '/HEIC/converted_webp/' . $webpFilename;
                } else {
                    $uploadError = 'Error uploading file.';
                }
            } catch (Exception $e) {
                error_log('Conversion error: ' . $e->getMessage());
                $uploadError = 'Invalid HEIC file or conversion error.';
                
                // Cleanup temporary files
                if (isset($heicPath) && file_exists($heicPath)) unlink($heicPath);
                if (isset($webpPath) && file_exists($webpPath)) unlink($webpPath);
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
    <title>Convert HEIC to WebP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css">
    <style>
        .container-wrapper { min-height: calc(100vh - 120px); display: flex; align-items: center; padding: 20px 0; }
        .conversion-box { border: 1px solid #ddd; border-radius: 5px; padding: 30px; margin-top: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .mt-20 { margin-top: 20px; }
        .mt-10 { margin-top: 10px; }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="container">
        <div class="container-wrapper">
            <div class="col-md-8 col-md-offset-2">
                <div class="conversion-box">
                    <h2 class="text-center">HEIC to WebP Converter</h2>
                    <p class="text-center text-muted">Convert HEIC/HEIF images to modern WebP format</p>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="form-group">
                            <label for="heicFile">Select HEIC/HEIF File:</label>
                            <input type="file" name="heicFile" class="form-control" 
                                   id="heicFile" accept=".heic,.heif" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="glyphicon glyphicon-convert"></span> Convert to WebP
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
                            <p>Your WebP file is ready:</p>
                            <a href="<?= htmlspecialchars($downloadLink) ?>" 
                               class="btn btn-success btn-block" download>
                               Download WebP
                            </a>
                            <div class="mt-10">
                                <a href="heic-to-webp.php" class="btn btn-default btn-block">
                                    Convert Another File
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
</body>
</html>