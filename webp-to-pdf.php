<?php
session_start(); // Start the session to manage state

// Initialize variables for file upload and conversion
$downloadLink = '';
$uploadError = '';

// Handle file upload and conversion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded
    if (isset($_FILES['webpFile']) && $_FILES['webpFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['webpFile']['tmp_name'];
        $fileName = $_FILES['webpFile']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the uploaded file is a WEBP
        if ($fileExtension === 'webp') {
            // Set the PDF file name
            $pdfFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';

            // Convert WEBP to PDF (using Imagick or other method)
            try {
                $imagick = new Imagick();
                $imagick->readImage($fileTmpPath);
                $imagick->setImageFormat('pdf');
                $imagick->writeImage($pdfFileName);
                $imagick->clear();
                $imagick->destroy();

                // Set the download link for the converted PDF
                $downloadLink = $pdfFileName;
                $_SESSION['download_link'] = $downloadLink; // Save download link in session
            } catch (Exception $e) {
                $uploadError = 'Error converting file: ' . $e->getMessage();
            }
        } else {
            $uploadError = 'Please upload a valid WEBP file.';
        }
    } else {
        $uploadError = 'Error uploading the file. Please try again.';
    }
}

// Check if a download link is available in the session
if (isset($_SESSION['download_link'])) {
    $downloadLink = $_SESSION['download_link'];
    unset($_SESSION['download_link']); // Clear the session variable after use
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert WEBP to PDF</title>

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
        <h2 class="text-center">Convert WEBP to PDF</h2>
        <p class="text-center">Upload a WEBP image file to convert it to PDF format.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="webpFile">Choose WEBP file:</label>
                <input type="file" name="webpFile" class="form-control" id="webpFile" accept=".webp" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Convert to PDF</button>
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
                <p>Conversion successful! Download your PDF file:</p>
                <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>Download PDF</a>
                <a href="webp-to-pdf.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
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
