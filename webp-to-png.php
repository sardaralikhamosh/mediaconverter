<?php
session_start();
$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['webpFile'])) {
    $file = $_FILES['webpFile'];
    
    // Verify that the uploaded file is a WEBP image
    if ($file['type'] == 'image/webp' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'webp') {
        // Define upload and converted directories
        $uploadDir = 'uploads/';
        $convertedDir = 'converted/';
        
        // Ensure directories exist, create them if they don’t
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        if (!is_dir($convertedDir)) mkdir($convertedDir, 0777, true);
        
        // Define paths for saving uploaded and converted files
        $webpPath = $uploadDir . basename($file['name']);
        $pngPath = $convertedDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.png';
        
        // Move the uploaded file and process the conversion
        if (move_uploaded_file($file['tmp_name'], $webpPath)) {
            // Convert WEBP to PNG using the GD library
            $webpImage = imagecreatefromwebp($webpPath);
            if ($webpImage) {
                if (imagepng($webpImage, $pngPath)) {
                    imagedestroy($webpImage); // Free up memory
                    $downloadLink = $pngPath; // Set the path for the download button
                } else {
                    $uploadError = 'Error saving PNG file.';
                }
            } else {
                $uploadError = 'Error converting WEBP to PNG. Please check the file format.';
            }
        } else {
            $uploadError = 'Error uploading the file. Please try again.';
        }
    } else {
        $uploadError = 'Please upload a valid WEBP file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert PDF to WEBP</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <!-- Optional Bootstrap Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom.css">
</head>
<body>

    <!-- Include Header -->
    <?php include 'header.php'; ?>

  <!--body starts-->
<div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
    <div class="content" style="width: 100%; max-width: 600px;">
        <h2 class="text-center">Convert WEBP to PNG</h2>
        <p class="text-center">Upload a WEBP file to convert it to PNG format.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="webpFile">Choose WEBP file:</label>
                <input type="file" name="webpFile" class="form-control" id="webpFile" accept=".webp" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Convert to PNG</button>
        </form>

        <!-- Display upload error if any -->
        <?php if ($uploadError): ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                <?= htmlspecialchars($uploadError) ?>
            </div>
        <?php endif; ?>

        <!-- Display download link and Go Back button if conversion is successful -->
        <?php if ($downloadLink): ?>
            <div class="alert alert-success" style="margin-top: 20px;">
                <p>Conversion successful! Download your PNG file:</p>
                <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>Download PNG</a>
                <a href="webp-to-png.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
            </div>
        <?php endif; ?>
    </div>
</div>
  <!--boday ends here -->

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
