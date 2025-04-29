<  <?php
session_start();
$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pngFile'])) {
    $file = $_FILES['pngFile'];
    
    // Verify that the uploaded file is a PNG image
    if ($file['type'] == 'image/png' || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'png') {
        // Define upload and converted directories
        $uploadDir = 'uploads/';
        $convertedDir = 'converted/';
        
        // Ensure directories exist, create them if they don’t
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        if (!is_dir($convertedDir)) mkdir($convertedDir, 0777, true);
        
        // Define paths for saving uploaded and converted files
        $pngPath = $uploadDir . basename($file['name']);
        $jpgPath = $convertedDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.jpg';
        
        // Move the uploaded file and process the conversion
        if (move_uploaded_file($file['tmp_name'], $pngPath)) {
            // Convert PNG to JPG using the GD library
            $pngImage = imagecreatefrompng($pngPath);
            if ($pngImage) {
                // Create a white background for the JPG
                $width = imagesx($pngImage);
                $height = imagesy($pngImage);
                $jpgImage = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($jpgImage, 255, 255, 255);
                imagefill($jpgImage, 0, 0, $white);
                imagecopy($jpgImage, $pngImage, 0, 0, 0, 0, $width, $height);

                // Save the image as JPG
                if (imagejpeg($jpgImage, $jpgPath, 90)) { // Quality set to 90
                    imagedestroy($pngImage); // Free up memory
                    imagedestroy($jpgImage);
                    $downloadLink = $jpgPath; // Set the path for the download button
                } else {
                    $uploadError = 'Error saving JPG file.';
                }
            } else {
                $uploadError = 'Error converting PNG to JPG. Please check the file format.';
            }
        } else {
            $uploadError = 'Error uploading the file. Please try again.';
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
    <title>Convert PNG to JPG</title>

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
        <h2 class="text-center">Convert PNG to JPG</h2>
        <p class="text-center">Upload a PNG file to convert it to JPG format.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pngFile">Choose PNG file:</label>
                <input type="file" name="pngFile" class="form-control" id="pngFile" accept=".png" required>
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
                <a href="png-to-jpg.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
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
