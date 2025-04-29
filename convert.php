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
    <?php include 'header.php'; ?>

<div class="container" style="margin-top: 10px;">
    <div class="main">
        <div class="container">
<?php
// CloudConvert API key
$apiKey = 'sAymeBrBPCVefMWiDfPSpgFF45yxoJDg2COvV6f5';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file and target format have been uploaded
    if (isset($_FILES['file']) && isset($_POST['target_format'])) {
        $file = $_FILES['file'];
        $targetFormat = strtolower($_POST['target_format']);

        // Allowed formats for input and output (including .avif and .jfif)
        $allowedFormats = ['jpeg', 'jpg', 'png', 'jfif', 'gif', 'webp', 'svg', 'psd', 'pdf', 'avif'];

        // Validate file extension
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedFormats)) {
            echo "Unsupported input format!";
            exit;
        }

        // Validate target format
        if (!in_array($targetFormat, $allowedFormats)) {
            echo "Unsupported target format!";
            exit;
        }

        // Temporary upload path
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the server
        $tempFilePath = $uploadDir . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
            echo "File upload failed!";
            exit;
        }

        // If the target format is SVG, use CloudConvert API
        if ($targetFormat === 'svg') {
            try {
                // First, create the job with a placeholder for the upload
                $postFields = json_encode([
                    "tasks" => [
                        "import-my-file" => [
                            "operation" => "import/upload"
                        ],
                        "convert-my-file" => [
                            "operation" => "convert",
                            "input" => "import-my-file",
                            "input_format" => $fileExtension,
                            "output_format" => $targetFormat
                        ],
                        "export-my-file" => [
                            "operation" => "export/url",
                            "input" => "convert-my-file"
                        ]
                    ]
                ]);

                // Initialize cURL for job creation
                $ch = curl_init('https://sync.api.cloudconvert.com/v2/jobs');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ]);

                // Execute the API call to create the job
                $response = curl_exec($ch);
                if ($response === false) {
                    die('Error during CloudConvert API request: ' . curl_error($ch));
                }

                // Decode the response
                $jsonResponse = json_decode($response, true);
                if (isset($jsonResponse['data']['id'])) {
                    $jobId = $jsonResponse['data']['id'];

                    // Now upload the file to CloudConvert
                    $uploadUrl = $jsonResponse['data']['tasks'][0]['result']['form']['url'];
                    $uploadFields = $jsonResponse['data']['tasks'][0]['result']['form']['parameters'];

                    // Initialize cURL for file upload
                    $chUpload = curl_init($uploadUrl);
                    curl_setopt($chUpload, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chUpload, CURLOPT_POST, true);
                    curl_setopt($chUpload, CURLOPT_POSTFIELDS, array_merge($uploadFields, [
                        'file' => new CURLFile($tempFilePath)
                    ]));

                    // Execute the file upload
                    $uploadResponse = curl_exec($chUpload);
                    if ($uploadResponse === false) {
                        die('Error during file upload: ' . curl_error($chUpload));
                    }
                    curl_close($chUpload);

                    // Now, check the job status and get the result URL
                    $checkJobResponse = curl_init("https://sync.api.cloudconvert.com/v2/jobs/$jobId");
                    curl_setopt($checkJobResponse, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($checkJobResponse, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $apiKey
                    ]);

                    // Execute to check job status
                    $jobStatusResponse = curl_exec($checkJobResponse);
                    curl_close($checkJobResponse);

                    $jobStatusJson = json_decode($jobStatusResponse, true);
                    if (isset($jobStatusJson['data']['tasks'][1]['result']['files'][0]['url'])) {
                        $convertedFileUrl = $jobStatusJson['data']['tasks'][1]['result']['files'][0]['url'];
                        echo "<div class='text-center'><a href='$outputFilePath' class='btn btn-primary'>Download Converted Image</a></div>";
                    } else {
                        echo "Conversion failed! Check your API key or input file.";
                    }

                } else {
                    echo "Failed to create CloudConvert job.";
                }

                // Close cURL session
                curl_close($ch);

            } catch (Exception $e) {
                echo "Error during image conversion: " . $e->getMessage();
            }
        } else {
            // Use Imagick for non-SVG formats
            // Use Imagick for non-SVG formats
try {
    $imagick = new Imagick($tempFilePath);

    // Check if target format is jfif and handle accordingly
    if ($targetFormat == 'jfif') {
        $imagick->setImageFormat('jpg');
        $outputFilePath = $uploadDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.jfif';
    } else {
        $imagick->setImageFormat($targetFormat);
        $outputFilePath = $uploadDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.' . $targetFormat;
    }

    // Save the converted image
    if ($targetFormat == 'pdf') {
        $imagick->setImageCompressionQuality(100);
    }
    $imagick->writeImage($outputFilePath);

    // Output success message with download link
    echo "<h1>Image converted successfully!</h1>";
    echo "<br>";
    echo "<div style='display: flex; gap: 10px;'>
            <a href='$outputFilePath' class='btn btn-primary btn-lg' download>Download</a>
            <a href='index.php' class='btn btn-primary btn-lg'>Go Back</a>
          </div>";

    // Clean up Imagick object
    $imagick->clear();
    $imagick->destroy();

    // Optionally, remove the temporary uploaded file
    unlink($tempFilePath);
} catch (Exception $e) {
    echo "Error during image conversion: " . $e->getMessage();
}
        }
    } else {
        echo "No file or target format selected!";
    }
} else {
    echo "Invalid request!";
}
?>
</div>
</div>
</div>
<?php include 'footer.php'; ?>
</body>
