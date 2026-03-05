<?php
// convert_documents.php - Updated with proper conversion handling
require_once __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Security checks
if (!isset($_GET['file']) || !file_exists($_GET['file'])) {
    die("Invalid file request.");
}

$inputFile = urldecode($_GET['file']);
$targetFormat = pathinfo($inputFile, PATHINFO_EXTENSION); // Get original format
$outputFormat = isset($_POST['target_format']) ? strtolower($_POST['target_format']) : 'pdf';

// Validate paths
$uploadDir = realpath('uploads');
$convertedDir = realpath('converted');
if (strpos(realpath($inputFile), $uploadDir) !== 0) {
    die("Invalid file path.");
}

// Conversion functions
function convertPdfToDocx($inputFile) {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($inputFile);
    $text = $pdf->getText();

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    $section->addText($text);
    
    $outputFile = 'converted/' . uniqid() . '.docx';
    $phpWord->save($outputFile);
    return $outputFile;
}

function convertToPdf($inputFile) {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($inputFile);
    $outputFile = 'converted/' . uniqid() . '.pdf';
    
    $domPdf = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
    $domPdf->save($outputFile);
    return $outputFile;
}

function convertToText($inputFile) {
    $content = '';
    
    if (pathinfo($inputFile, PATHINFO_EXTENSION) === 'pdf') {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($inputFile);
        $content = $pdf->getText();
    } else {
        $content = file_get_contents($inputFile);
    }
    
    $outputFile = 'converted/' . uniqid() . '.txt';
    file_put_contents($outputFile, $content);
    return $outputFile;
}

// Perform conversion
try {
    switch (strtolower(pathinfo($inputFile, PATHINFO_EXTENSION))) {
        case 'pdf':
            $outputFile = match($outputFormat) {
                'docx' => convertPdfToDocx($inputFile),
                'txt' => convertToText($inputFile),
                default => throw new Exception("Unsupported conversion format")
            };
            break;
            
        case 'docx':
        case 'doc':
            $outputFile = match($outputFormat) {
                'pdf' => convertToPdf($inputFile),
                'txt' => convertToText($inputFile),
                default => throw new Exception("Unsupported conversion format")
            };
            break;
            
        case 'txt':
        case 'csv':
            $outputFile = match($outputFormat) {
                'pdf' => convertToPdf($inputFile),
                'docx' => convertToText($inputFile), // TXT to DOCX not directly supported
                default => throw new Exception("Unsupported conversion format")
            };
            break;
            
        default:
            throw new Exception("Unsupported file type");
    }

    // Deliver file
    if (file_exists($outputFile)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($outputFile).'"');
        header('Content-Length: ' . filesize($outputFile));
        readfile($outputFile);
        
        // Cleanup
        unlink($inputFile);
        unlink($outputFile);
        exit;
    }

} catch (Exception $e) {
    // Cleanup on error
    if (isset($inputFile) && file_exists($inputFile)) unlink($inputFile);
    if (isset($outputFile) && file_exists($outputFile)) unlink($outputFile);
    
    die("Conversion error: " . $e->getMessage());
}