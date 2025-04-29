<?php
session_start(); // Start session to manage state

// Initialize variables for file upload and conversion
$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pngFile'])) {
    $file = $_FILES['pngFile'];

    // Check if the uploaded file is a PNG
    if ($file['type'] == 'image/png') {
        $uploadDir = 'uploads/';
        $pngPath = $uploadDir . basename($file['name']);

        // Move the uploaded PNG file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $pngPath)) {
            // Set the output path for the converted AVIF file
            $avifPath = 'converted/' . pathinfo($pngPath, PATHINFO_FILENAME) . '.avif';

            // Convert PNG to AVIF using Imagick
            try {
                $imagick = new Imagick($pngPath);
                $imagick->setImageFormat('avif'); // Set format to AVIF
                $imagick->writeImage($avifPath);
                $imagick->clear();
                $imagick->destroy();

                $downloadLink = $avifPath; // Set the path for the download button
            } catch (Exception $e) {
                $uploadError = 'Error converting file: ' . $e->getMessage();
            }
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Please upload a valid PNG file.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert PNG to AVIF</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <!-- Optional Bootstrap Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom.css">
</head>
<body>

    <!-- Include Header -->
    <?php include 'header.php'; ?>

    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
        <div class="content" style="width: 100%; max-width: 600px;">
            <h2 class="text-center">Convert PNG to AVIF</h2>
            <p class="text-center">Upload a PNG file to convert it to AVIF format.</p>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pngFile">Choose PNG file:</label>
                    <input type="file" name="pngFile" class="form-control" id="pngFile" accept=".png" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Convert to AVIF</button>
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
                    <p>Conversion successful! Download your AVIF file:</p>
                    <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>Download AVIF</a>
                    <a href="png-to-avif.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
