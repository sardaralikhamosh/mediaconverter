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
    } elseif (isset($_FILES['jfifFile'])) {
        $file = $_FILES['jfifFile'];

        // Validate file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $uploadError = 'File size too large (max 10MB)';
        } else {
            try {
                // Validate JFIF file using Imagick
                $imagick = new Imagick($file['tmp_name']);
                $format = $imagick->getImageFormat();
                if (strtoupper($format) !== 'JPEG') {
                    throw new Exception('Invalid JFIF file');
                }
                $imagick->clear();

                // Set directory paths
                $baseDir = __DIR__;
                $uploadDir = $baseDir . '/jfif_uploads/';
                $convertedDir = $baseDir . '/converted_heic/';
                
                // Create directories if missing
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!file_exists($convertedDir)) mkdir($convertedDir, 0755, true);

                // Generate unique filenames
                $filename = uniqid() . '.jfif';
                $jfifPath = $uploadDir . $filename;
                $heicFilename = pathinfo($filename, PATHINFO_FILENAME) . '.heic';
                $heicPath = $convertedDir . $heicFilename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $jfifPath)) {
                    // Convert JFIF to HEIC
                    $imagick = new Imagick($jfifPath);
                    $imagick->setImageFormat('heic');
                    $imagick->setImageCompressionQuality(90);
                    $imagick->writeImage($heicPath);
                    $imagick->clear();
                    $imagick->destroy();

                    // Cleanup files
                    if (file_exists($jfifPath)) unlink($jfifPath);

                    // Set web path for download
                    $downloadLink = '/HEIC/converted_heic/' . $heicFilename;
                } else {
                    $uploadError = 'Error uploading file.';
                }
            } catch (Exception $e) {
                error_log('Conversion error: ' . $e->getMessage());
                $uploadError = 'Invalid JFIF file or conversion error.';
                
                // Cleanup temporary files
                if (isset($jfifPath) && file_exists($jfifPath)) unlink($jfifPath);
                if (isset($heicPath) && file_exists($heicPath)) unlink($heicPath);
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
    <title>Convert JFIF to HEIC</title>
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
                    <h2 class="text-center">JFIF to HEIC Converter</h2>
                    <p class="text-center text-muted">Convert JFIF images to HEIC format</p>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="form-group">
                            <label for="jfifFile">Select JFIF File:</label>
                            <input type="file" name="jfifFile" class="form-control" 
                                   id="jfifFile" accept=".jfif" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="glyphicon glyphicon-convert"></span> Convert to HEIC
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
                            <p>Your HEIC file is ready:</p>
                            <a href="<?= htmlspecialchars($downloadLink) ?>" 
                               class="btn btn-success btn-block" download>
                               Download HEIC
                            </a>
                            <div class="mt-10">
                                <a href="jfif-to-heic.php" class="btn btn-default btn-block">
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
