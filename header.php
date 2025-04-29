<head> 
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <!-- Optional Bootstrap Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <style>
        /* Full-width dropdown on mobile */
        @media (max-width: 768px) {
            #navbar-collapse {
                position: absolute;
                top: 50px; /* Adjust based on navbar height */
                left: 0;
                width: 100%;
                background-color: #fff; /* Match navbar background */
                z-index: 1;
            }
            /* Two-column row on mobile */
            .navbar-header, .navbar-collapse {
                float: none;
                text-align: left;
            }
            .navbar-header {
                display: flex;
                justify-content: space-between;
                width: 100%;
            }
            .fullwidthcontainer{
                width: 100%;
            }
            .col-xs-8{
                width:100%;
            }
        }
        @media screen and (min-width: 624px) {
        .row.fullwidthcontainer {
            width: 100%;
            }
        ul.nav.navbar-nav {
                display: block;
            }
        }
    </style>
</head>
<header>
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="row fullwidthcontainer">
                <!-- Logo and Hamburger (Mobile) -->
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="navbar-header">
                        <!-- Brand Logo -->
                        <a class="navbar-brand" href="/index.php">CONVERTER</a>
                        
                        <!-- Hamburger button for mobile -->
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                </div>

                <!-- Navbar Links -->
                <div class="col-sm-8 col-lg-8 col-md-8 col-xs-8">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="./index.php/#image">Image</a></li>
                            <li><a href="/document.php">Document</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- jQuery and Bootstrap JS for collapsible behavior -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
