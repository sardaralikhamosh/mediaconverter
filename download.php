<?php
// download.php
if (isset($_GET['file']) && isset($_GET['original'])) {
    $file = $_GET['file'];
    $originalFile = $_GET['original'];
    
    if (file_exists($file)) {
        // Set headers to download the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        flush(); // Flush system output buffer
        readfile($file);

        // Delete both the uploaded and converted files after download
        unlink($file);
        if (file_exists($originalFile)) {
            unlink($originalFile);
        }
        
        exit;
    } else {
        echo "File not found.";
    }
}
?>
