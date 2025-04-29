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

    <div class="main" style="margin-top: 10px;">
        <div class="container">
            <!-- Image Upload and Conversion Form -->
            <h2 class="text-center">Convert Image Files</h2>
            <p class="text-center">Convert any image to JPG, PNG, JFIF, GIF, JPEG, SVG, PSD, PDF, or WEBP.</p>
            <form class="file-upload-form" method="post" enctype="multipart/form-data" action="convert.php">
                <div class="file-upload-design">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control" required />
                    </div>
                    
                    <div class="form-group">
                        <select name="source_format" class="form-control" required>
                            <option value="" disabled selected>Select source format</option>
                            <option value="jpg">JPG</option>
                            <option value="png">PNG</option>
                            <option value="gif">GIF</option>
                            <option value="jpeg">JPEG</option>
                            <option value="psd">PSD</option>
                            <option value="pdf">PDF</option>
                            <option value="webp">WEBP</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select name="target_format" class="form-control" required>
                            <option value="" disabled selected>Select target format</option>
                            <option value="jpg">JPG</option>
                            <option value="png">PNG</option>
                            <option value="gif">GIF</option>
                            <option value="jpeg">JPEG</option>
                            <option value="psd">PSD</option>
                            <option value="pdf">PDF</option>
                            <option value="webp">WEBP</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Convert Image</button>
                </div>
            </form>

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
                            <option value="doc">DOC/DOCX</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select name="target_format" class="form-control" required>
                            <option value="" disabled selected>Select target format</option>
                            <option value="txt">TXT</option>
                            <option value="pdf">PDF</option>
                            <option value="doc">DOC/DOCX</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Convert Document</button>
                </div>
            </form>

            <div class="result" id="result"></div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'footer.php'; ?>

    <!-- jQuery and Bootstrap JS for collapsible behavior -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfO8fw5SYs5xY5d6p39H9AaKq10M0ZT+pl5g1K+fo" crossorigin="anonymous"></script>

</body>
</html>
