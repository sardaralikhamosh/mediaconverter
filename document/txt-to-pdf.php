<?php 
session_start(); // Start session to manage state

// Initialize variables for file upload and conversion
$uploadError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['txtFile'])) {
    $file = $_FILES['txtFile'];

    // Check if the uploaded file is a TXT
    $fileType = mime_content_type($file['tmp_name']);
    if ($fileType === 'text/plain') { // Corrected MIME type
        $uploadDir = '../uploads/';
        $convertedDir = '../converted/';

        // Create directories if they do not exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($convertedDir)) {
            mkdir($convertedDir, 0755, true);
        }

        // Generate a unique filename to avoid conflicts
        $txtPath = $uploadDir . uniqid() . '_' . basename($file['name']);
        
        // Move the uploaded TXT file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $txtPath)) {
            // Set the output path for the converted PDF file
            $pdfPath = $convertedDir . pathinfo($txtPath, PATHINFO_FILENAME) . '.pdf';

            // Convert TXT to PDF using FPDF
            try {
                require_once __DIR__ . '/../fpdf/fpdf.php'; // Load the FPDF library
                if (!class_exists('FPDF')) {
                    throw new Exception('FPDF library not found. Please check the path.');
                }

                $pdf = new FPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'UTF-8', 12);

                // Read the content of the TXT file
                $content = file_get_contents($txtPath);
                if ($content === false) {
                    throw new Exception('Failed to read the TXT file.');
                }

                foreach (explode("\n", $content) as $line) {
                    $pdf->Cell(0, 10, $line, 0, 1); // Add each line to the PDF
                }
                
                $pdf->Output('F', $pdfPath); // Save the PDF to the file system
                $downloadLink = $pdfPath; // Set the path for the download button
            } catch (Exception $e) {
                $uploadError = 'Error converting TXT to PDF: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $uploadError = 'Error uploading file.';
        }
    } else {
        $uploadError = 'Please upload a TXT file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert TXT to PDF</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <!-- Optional Bootstrap Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../custom.css">
</head>
<body>

    <!-- Include Header -->
    <?php include '../header.php'; ?>

    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh; margin-top: 0;">
        <div class="content" style="width: 100%; max-width: 600px;">
            <h2 class="text-center">Convert TXT to PDF</h2>
            <p class="text-center">Upload a TXT file to convert it to PDF format.</p>
            
            <form action="txt-to-pdf.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="txtFile">Choose TXT file:</label>
                    <input type="file" name="txtFile" class="form-control" id="txtFile" accept=".txt" required>
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
                    <a href="txt-to-pdf.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Go Back</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../footer.php'; ?>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
