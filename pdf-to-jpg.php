<?php
session_start(); // Start session to manage state

// Initialize variables for file upload and conversion
$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFile'])) {
    $file = $_FILES['pdfFile'];

    // Check if the uploaded file is a PDF
    $fileType = mime_content_type($file['tmp_name']);
    if ($fileType === 'application/pdf') {
        $uploadDir = 'uploads/';
        $pdfPath = $uploadDir . basename($file['name']);

        // Move the uploaded PDF file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $pdfPath)) {
            // Set the output path for the converted JPG file
            $jpgPath = 'converted/' . pathinfo($pdfPath, PATHINFO_FILENAME) . '.jpg';

            // Convert PDF to JPG using Imagick
            try {
                $imagick = new Imagick();
                $imagick->setResolution(300, 300); // Set resolution for high-quality conversion
                $imagick->readImage($pdfPath . '[0]'); // Read the first page of the PDF
                $imagick->setImageFormat('jpeg');
                $imagick->writeImage($jpgPath);
                $imagick->clear();
                $imagick->destroy();

                $downloadLink = $jpgPath; // Set the path for the download button
            } catch (Exception $e) {
                $uploadError = 'Error converting PDF to JPG: ' . $e->getMessage();
            }
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Please upload a PDF file.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert PDF to JPG</title>

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

    <!-- Main content starts -->
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
        <div class="content" style="width: 100%; max-width: 600px;">
            <h2 class="text-center">Convert PDF to JPG</h2>
            <p class="text-center">Upload a PDF file to convert it to JPG format.</p>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pdfFile">Choose PDF file:</label>
                    <input type="file" name="pdfFile" class="form-control" id="pdfFile" accept=".pdf" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Convert to JPG</button>
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
                    <p>Conversion successful! Download your JPG file:</p>
                    <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>Download JPG</a>
                    <a href="pdf-to-jpg.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main content ends -->

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
