<?php
// Path to the upload folder
$uploadDir = 'upload/';
$uploadedFile = $uploadDir . basename($_FILES['docFile']['name']);

// Move the uploaded file to the upload folder
if (move_uploaded_file($_FILES['docFile']['tmp_name'], $uploadedFile)) {
    // Conversion code goes here
    
    // Assume that $convertedFile contains the path to the converted file

    // Set headers to prompt download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($convertedFile) . '"');
    header('Content-Length: ' . filesize($convertedFile));

    // Read and output the converted file
    readfile($convertedFile);

    // Delete the uploaded and converted files after download
    unlink($uploadedFile);  // Delete original uploaded file
    unlink($convertedFile);  // Delete the converted file
    exit;
} else {
    echo "File upload failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image and Document Converter</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- Optional Bootstrap Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom.css">
    
        <style>
            /*custom css for new idex file by sardaralikhamosh@gmail.com*/
        .bgcolumn{
            background: lightgray;
        }
        /*custom for new index is closed*/
        
        /*for mobile only start*/
        @media screen and (max-width: 624px){
            .row.mg-5.justify-content-between {
                display: contents;
            }
            .marginpagecustom{
                margin: 150px 0 50px 0;
            }
            .margincolumnscustom{
                margin-top:10px;
            }
            .custom-margin-t-b{
                margin: 20px 0;
            }
        }
        /*for mobile ends*/
        /*        for laptop starts*/
            @media screen and (min-width: 624px){
                .marginpagecustom{
                margin: 150px 0 50px 0;
            }
            .custom-margin-t-b{
                margin: 20px 0;
            }
            }
        </style>
        
</head>
<body>

    <!-- Include Header -->
    <?php include 'header.php'; ?>

        <!-- document section starts -->
        <section class="custom-margin-t-b .text-center justify-content-center d-flex" style="min-height:100vh; margin-top:20px">
            <div class="container justify-content-center ">
            <!-- Document Upload and Conversion Form -->
            <h2 class="text-center mt-5">Convert Document Files</h2>
            <p class="text-center">Convert PDF, DOC, HTML, CSV, TXT, or RTF files to different formats.</p>
            <form class="file-upload-form" method="post" enctype="multipart/form-data" action="convert_documents.php">
                <div class="file-upload-design">
                    <div class="form-group">
                        <input type="file" name="docFile" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <select name="source_format" class="form-control" required>
                            <option value="" disabled selected>Select source format</option>
                            <option value="txt">TXT</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select name="target_format" class="form-control" required>
                            <option value="" disabled selected>Select target format</option>
                            <option value="txt">TXT</option>
                            <option value="pdf">PDF</option>
                            <option value="doc">DOCX</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Convert Document</button>
                </div>
            </form>

            <div class="result" id="result"></div>
        </div>
        </section>
<!-- document section ends here -->
    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS for collapsible behavior -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
