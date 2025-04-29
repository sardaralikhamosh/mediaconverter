<?php 
session_start(); // Start the session to manage state

// Initialize variables for file upload and conversion
$downloadLink = '';
$uploadError = '';

// Handle file upload and conversion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdfFile']['tmp_name'];
        $fileName = $_FILES['pdfFile']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the uploaded file is a PDF
        if ($fileExtension === 'pdf') {
            // Set the WEBP file name
            $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

            // Convert PDF to WEBP (using Imagick or other method)
            try {
                $imagick = new Imagick();
                $imagick->setResolution(300, 300); // Set resolution for better quality
                $imagick->readImage($fileTmpPath . '[0]'); // Read the first page of the PDF
                $imagick->setImageFormat('webp');
                $imagick->writeImage($webpFileName);
                $imagick->clear();
                $imagick->destroy();

                // Set the download link for the converted WEBP
                $downloadLink = $webpFileName;
                $_SESSION['download_link'] = $downloadLink; // Save download link in session
            } catch (Exception $e) {
                $uploadError = 'Error converting file: ' . $e->getMessage();
            }
        } else {
            $uploadError = 'Please upload a valid PDF file.';
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

  <!-- Body starts -->
  <div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
    <div class="content" style="width: 100%; max-width: 600px;">
        <h2 class="text-center">Convert PDF to WEBP</h2>
        <p class="text-center">Upload a PDF file to convert it to WEBP format.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pdfFile">Choose PDF file:</label>
                <input type="file" name="pdfFile" class="form-control" id="pdfFile" accept=".pdf" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Convert to WEBP</button>
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
                <p>Conversion successful! Download your WEBP file:</p>
                <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-success btn-block" download>Download WEBP</a>
                <a href="pdf-to-webp.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
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
