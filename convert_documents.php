<?php
require_once __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define paths
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('CONVERTED_DIR', __DIR__ . '/converted/');

// Verify parameters
if (!isset($_GET['file']) || !isset($_GET['target'])) {
    die("Missing required parameters");
}

// Get parameters
$fileName = basename($_GET['file']); // Sanitize filename
$targetFormat = strtolower($_GET['target']);
$inputFile = UPLOAD_DIR . $fileName;
$outputFile = '';

try {
    // Validate paths
    if (!file_exists($inputFile)) {
        throw new Exception("File not found: " . $fileName);
    }

    // Security check
    $realUploadPath = realpath(UPLOAD_DIR);
    $realInputPath = realpath($inputFile);
    
    if ($realInputPath === false || strpos($realInputPath, $realUploadPath) !== 0) {
        throw new Exception("Invalid file path");
    }

    // Supported conversions
    $conversionMap = [
        'pdf' => ['docx', 'txt'],
        'docx' => ['pdf', 'txt'],
        'txt' => ['pdf', 'docx']
    ];

    $fileType = strtolower(pathinfo($inputFile, PATHINFO_EXTENSION));
    
    // Validate conversion
    if (!isset($conversionMap[$fileType]) || !in_array($targetFormat, $conversionMap[$fileType])) {
        throw new Exception("Unsupported conversion: $fileType to $targetFormat");
    }

    // Perform conversion
    switch ("$fileType-to-$targetFormat") {
        case 'pdf-to-docx':
            $outputFile = convertPdfToDocx($inputFile);
            break;
            
        case 'docx-to-pdf':
            $outputFile = convertDocxToPdf($inputFile);
            break;
            
        default:
            throw new Exception("Conversion handler not implemented");
    }

    // Deliver file
    if (file_exists($outputFile)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
        readfile($outputFile);
        
        // Cleanup
        unlink($inputFile);
        unlink($outputFile);
        exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    die("Error: " . $e->getMessage());
}
// pdf to docs function 
function convertPdfToDocx($inputFile) {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($inputFile);
    
    // Create temporary directory for images
    $tempDir = sys_get_temp_dir() . '/pdf_images_' . uniqid();
    mkdir($tempDir, 0755, true);

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    // Extract content with images
    $content = '';
    $pages = $pdf->getPages();
    
    foreach ($pages as $page) {
        // Extract text
        $text = $page->getText();
        $section->addText($text);

        // Extract images
        $objects = $page->getXObjects();
        foreach ($objects as $object) {
            if ($object instanceof \Smalot\PdfParser\XObject\Image) {
                $imagePath = $tempDir . '/image_' . uniqid() . '.' . $object->getExtension();
                file_put_contents($imagePath, $object->getContent());
                
                // Add image to Word document
                $section->addImage(
                    $imagePath,
                    [
                        'width' => $object->getWidth() * 0.75, // Convert points to pixels
                        'height' => $object->getHeight() * 0.75,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
                    ]
                );
            }
        }
    }

    // Cleanup temporary files
    array_map('unlink', glob("$tempDir/*.*"));
    rmdir($tempDir);

    $outputFile = CONVERTED_DIR . uniqid() . '.docx';
    $phpWord->save($outputFile);
    
    return $outputFile;
}

function convertDocxToPdf($inputFile) {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($inputFile);
    $outputFile = CONVERTED_DIR . uniqid() . '.pdf';
    
    $domPdf = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
    $domPdf->save($outputFile);
    return $outputFile;
}