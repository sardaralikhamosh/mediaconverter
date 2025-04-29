<?php
session_start();

$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['imageFile'])) {
    $file = $_FILES['imageFile'];

    // Check if the uploaded file is a JFIF image
    if ($file['type'] == 'image/jpeg' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'jfif') {
        $uploadDir = 'uploads/';
        $jfifPath = $uploadDir . basename($file['name']);

        // Move the uploaded JFIF file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $jfifPath)) {
            // Set the output path for the converted AVIF file
            $avifPath = 'converted/' . pathinfo($jfifPath, PATHINFO_FILENAME) . '.avif';

            // Convert JFIF to AVIF using Imagick
            try {
                $imagick = new Imagick($jfifPath);
                $imagick->setImageFormat('avif');
                $imagick->writeImage($avifPath);
                $imagick->clear();
                $imagick->destroy();

                $downloadLink = $avifPath;
            } catch (Exception $e) {
                $uploadError = 'Error converting file: ' . $e->getMessage();
            }
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Please upload a valid JFIF file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert JFIF to AVIF</title>

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

    <!-- Body starts -->
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
        <div class="content" style="width: 100%; max-width: 600px;">
            <h2 class="text-center">Convert JFIF to AVIF</h2>
            <p class="text-center">Upload a JFIF file to convert it to AVIF format.</p>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="imageFile">Choose JFIF file:</label>
                    <input type="file" name="imageFile" class="form-control" id="imageFile" accept=".jfif, .jpeg" required>
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
                    <a href="jfif-to-avif.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Body ends here -->

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
