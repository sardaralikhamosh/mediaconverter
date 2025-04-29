<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image and Document Converter</title>

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

<div class="container" style="margin-top: 10px;">
    <div class="main">
        <div class="container">
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload Composer libraries
require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_FILES['docFile'];
    $target_format = strtolower($_POST['target_format']); // Ensure lowercase format
    
    // Validate the uploaded file and target format
    $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'csv'];
    $file_tmp = $file['tmp_name'];
    $file_name = $file['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions) || !in_array($target_format, $allowed_extensions)) {
        die("Unsupported file extension or conversion format.");
    }

    // Target directory and new file name generation
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $new_file_name = $target_dir . uniqid() . '_' . pathinfo($file_name, PATHINFO_FILENAME) . '.' . $target_format;

    // Check if the file was uploaded successfully
    if (move_uploaded_file($file_tmp, $target_dir . $file_name)) {
        switch (true) {
            // TXT to CSV
            case ($file_ext == 'txt' && $target_format == 'csv'):
                echo "Converting TXT to CSV...";
                convertTxtToCsv($target_dir . $file_name, $new_file_name);
                break;

            // PDF to CSV
            case ($file_ext == 'pdf' && $target_format == 'csv'):
                echo "Converting PDF to CSV...";
                convertPdfToCsv($target_dir . $file_name, $new_file_name);
                break;

            // PDF to TXT
            case ($file_ext == 'pdf' && $target_format == 'txt'):
                echo "<h1>Converting PDF to TXT...</h1>";
                convertPdfToTxt($target_dir . $file_name, $new_file_name);
                break;

            // TXT to PDF
            case ($file_ext == 'txt' && $target_format == 'pdf'):
                echo "Converting TXT to PDF...";
                convertTxtToPdf($target_dir . $file_name, $new_file_name);
                break;

            // DOC/DOCX to other formats
            case (in_array($file_ext, ['doc', 'docx']) && in_array($target_format, ['txt', 'csv', 'pdf'])):
                echo "Converting DOCX to " . strtoupper($target_format) . "...";
                convertDocxToOther($target_dir . $file_name, $new_file_name, $target_format);
                break;

            // PDF to DOCX/DOC
            case ($file_ext == 'pdf' && in_array($target_format, ['doc', 'docx'])):
                echo "Converting PDF to DOCX...";
                $new_docx_file_name = $target_dir . uniqid() . '_' . pathinfo($file_name, PATHINFO_FILENAME) . '.docx';
                if (convertPdfToDocx($target_dir . $file_name, $new_docx_file_name)) {
                    if ($target_format == 'doc') {
                        rename($new_docx_file_name, $new_file_name); // Rename DOCX to DOC
                    } else {
                        $new_file_name = $new_docx_file_name;
                    }
                }
                break;

            // Unsupported format case
            default:
                die("Unsupported file extension or conversion format.");
        }

        // After successful conversion, provide download link
        // echo '<br><a href="' . $new_file_name . '" download>Download Converted ' . strtoupper($target_format) . '</a>';
        // echo '<br><a href="/index.php">Go Back</a>';
        echo "<br>";
        echo "<br>";
       echo "<div style='display: flex; gap: 10px;'>
        <a href='download.php?file=" . urlencode($new_file_name) . "&original=" . urlencode($target_dir . $file_name) . "' class='btn btn-primary btn-lg'>Download Converted " . strtoupper($target_format) . "</a>
        <a href='/index.php' class='btn btn-primary btn-lg'>Go Back</a>
      </div>";

    } else {
        echo "Failed to upload file.";
    }
}

// Function to convert TXT to PDF using FPDF
function convertTxtToPdf($source, $destination) {
    $pdf = new \FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    $lines = file($source); // Read the file into an array of lines
    foreach ($lines as $line) {
        $pdf->Cell(0, 10, utf8_decode(trim($line)), 0, 1); // Add each line to the PDF
    }

    $pdf->Output('F', $destination); // Save the PDF to the destination
}

// Function to convert TXT to CSV
function convertTxtToCsv($source, $destination) {
    $lines = file($source);
    $fp = fopen($destination, 'w');
    foreach ($lines as $line) {
        fputcsv($fp, str_getcsv($line)); // Convert each line to CSV format
    }
    fclose($fp);
}

// Function to convert PDF to CSV
function convertPdfToCsv($source, $destination) {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($source);
    $text = $pdf->getText();

    $lines = explode("\n", $text);
    $fp = fopen($destination, 'w');
    foreach ($lines as $line) {
        fputcsv($fp, str_getcsv($line));
    }
    fclose($fp);
}

// Function to convert PDF to TXT
function convertPdfToTxt($source, $destination) {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($source);
    $text = $pdf->getText();
    file_put_contents($destination, $text);
}

// Function to convert PDF to DOCX
function convertPdfToDocx($source, $destination) {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($source);
    $text = $pdf->getText();

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    $section->addText($text);

    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($destination);
    return true;
}

// Function to convert DOCX to TXT, CSV, PDF (handling TextRun and hyperlinks for PDF)
function convertDocxToOther($source, $destination, $target_format) {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($source); // Load the DOCX file

    if ($target_format == 'txt') {
        // Convert DOCX to plain text (TXT)
        $textContent = '';
        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                // Check if the element has getText method (for text elements)
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                            $textContent .= $textElement->getText() . PHP_EOL;
                        } elseif ($textElement instanceof \PhpOffice\PhpWord\Element\Link) {
                            // Append hyperlinks in text form with URL
                            $textContent .= $textElement->getText() . ' (' . $textElement->getSource() . ')' . PHP_EOL;
                        }
                    }
                } elseif (method_exists($element, 'getText')) {
                    $textContent .= $element->getText() . PHP_EOL;
                }
            }
        }
        file_put_contents($destination, $textContent); // Save TXT file

    } elseif ($target_format == 'csv') {
        // Convert DOCX to CSV format
        $textContent = '';
        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                            $textContent .= $textElement->getText() . ','; // CSV uses commas to separate fields
                        } elseif ($textElement instanceof \PhpOffice\PhpWord\Element\Link) {
                            // Append hyperlinks in CSV format with URL
                            $textContent .= $textElement->getText() . ' (' . $textElement->getSource() . '),';
                        }
                    }
                } elseif (method_exists($element, 'getText')) {
                    $textContent .= $element->getText() . ','; // CSV field separator
                }
            }
        }
        $csvContent = rtrim($textContent, ','); // Remove the trailing comma
        file_put_contents($destination, $csvContent); // Save CSV file

    } elseif ($target_format == 'pdf') {
        // Convert DOCX to PDF using FPDF
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);

        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    // TextRun contains multiple text elements (Text, Link, etc.)
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                            // Write regular text to PDF
                            $pdf->Write(10, utf8_decode($textElement->getText()));
                        } elseif ($textElement instanceof \PhpOffice\PhpWord\Element\Link) {
                            // Write hyperlinks with blue, underlined text in PDF
                            $pdf->SetTextColor(0, 0, 255); // Set text color to blue
                            $pdf->SetFont('', 'U'); // Underline the text
                            $pdf->Write(10, utf8_decode($textElement->getText()), $textElement->getSource());
                            $pdf->SetFont(''); // Reset font to default
                            $pdf->SetTextColor(0, 0, 0); // Reset text color to black
                        }
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Link) {
                    // Handle standalone links in DOCX
                    $pdf->SetTextColor(0, 0, 255); // Set color to blue
                    $pdf->SetFont('', 'U'); // Underline the text
                    $pdf->Write(10, utf8_decode($element->getText()), $element->getSource());
                    $pdf->SetFont(''); // Reset to default
                    $pdf->SetTextColor(0, 0, 0); // Reset to black
                } elseif (method_exists($element, 'getText')) {
                    // Handle regular text elements
                    $pdf->MultiCell(0, 10, utf8_decode($element->getText()));
                }
            }
        }

        $pdf->Output('F', $destination); // Save the PDF to the destination
    }
}



?>
</div>
</div>
</div>
<?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS for collapsible behavior -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>
</body>