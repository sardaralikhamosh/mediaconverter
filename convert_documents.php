<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Ensure ConvertAPI library is installed via Composer

use ConvertApi\ConvertApi;

define('CONVERTAPI_SECRET', 'secret_wFmc9lQ8c7y8YDhC');
define('CONVERTED_DIR', '/home/u194258631/domains/dezinegenius.com/public_html/mediaconverter/converted_files/');

function convertPdfToDocx($inputFile) {
    ConvertApi::setApiCredentials(CONVERTAPI_SECRET);
    
    $result = ConvertApi::convert('docx', [
        'File' => $inputFile,
        'FileName' => 'converted_file',
        'Password' => '', // Add password if needed
        'Wysiwyg' => 'true',
    ], 'pdf');
    
    // Ensure the output directory exists
    if (!is_dir(CONVERTED_DIR)) {
        mkdir(CONVERTED_DIR, 0755, true);
    }
    
    $savedFiles = $result->saveFiles(CONVERTED_DIR);
    
    return $savedFiles[0] ?? null; // Return the first converted file path
}

// Example Usage
if (isset($_GET['file']) && $_GET['target'] === 'docx') {
    $inputFile = '/home/u194258631/domains/dezinegenius.com/public_html/mediaconverter/uploads/' . $_GET['file'];
    
    if (!file_exists($inputFile)) {
        die('File not found.');
    }
    
    $convertedFile = convertPdfToDocx($inputFile);
    
    if ($convertedFile) {
        echo "Conversion successful: <a href='$convertedFile'>Download DOCX</a>";
    } else {
        echo "Conversion failed.";
    }
}
?>
