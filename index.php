<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- JQuery and Bootstrap -->
  <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
  <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous" /> -->
  <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- Stylesheets -->
  <link rel="stylesheet" href="styles/variables.css" />
  <link rel="stylesheet" href="styles/index.css" />

  <!-- Google font -->
  <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@100;400;700&display=swap" rel="stylesheet"> -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital@0;1&display=swap" rel="stylesheet">

  <!-- Javascript -->
  <script defer src='js/endianReader.js'></script>
  <script src='js/exifImage.js'></script>
  <script defer src='js/uploader.js'></script>
  <script defer src='js/menus.js'></script>
  <script defer src='js/support.js'></script>
  <script defer src='js/mapping.js'></script>

 
  <!-- Leaflets mapping
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script> -->

  <title>EXIF Miner</title>
</head>

<body>
  <?php include('php/navbar.php'); 
  include ('php/aboutModal.php');
  include ('php/GPS/GPSModal.php');
  include ('php/EXIF/editTagsModal.php');
  
  ?>


    <div class='container-fluid mainWindow'>
      <div class='uploaderCanvas'>
        <div id="openfile" class="container">
          <form name="uploadForm" id="uploadForm" method="post" action="upload.php" enctype="multipart/form-data">
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="customFile" name="customFile" accept="jpg, cr2|image/*">
              <label class="custom-file-label btn btn-primary" for="customFile">Choose file</label>
            </div>
          </form>
          <div id="dropZone"><img src="assets/draganddrop.png" class="dragdropfield mx-auto">
            <h3 style="text-align: center; margin: 20px;">Drop your file here to upload your image</h3>
          </div>
        </div>
      </div>
      <div id="uploadProgress"></div>
      <div id="fileType"></div>
    
      <div class='row' style="height: 120vh; overflow-y: scroll;">
        <div class="col-xl-7">
          <div class='imageView isHidden col-xl-12'></div>
          <div class='map isHidden col-xl-12' id='map'></div>   
        </div>
        <div class='col-xl-3' style="height: 95vh; display: block; overflow-y: scroll; margin-bottom: 20px;">
          <div class='basicEXIFView isHidden col-xl-12'></div>
          <div class="fullEXIFView isHidden col-xl-12"></div>
        </div>
        <div id='histogramsBox' class='container col-xl-2 col-lg-6 isHidden' style=""> 
          <div id='HRGBCanvas' class="col-xl-10 offset-xl-1 col-sm-4 offset-sm-1 histogramCanvas sectionTitle">
            <h3>Histograms</h3>
            <canvas id="RGBCanvas" class="RGBCanvas"></canvas>
          </div>
          <div id='HLuminanceCanvas' class="col-xl-10 offset-xl-1 col-sm-4 offset-sm-1 histogramCanvas sectionTitle" style="margin-top: 50px;height: 120px;">
            <canvas id="LuminanceCanvas"></canvas>
          </div>
          <div id="HistogramStats" class="col-xl-9 offset-xl-1 col-sm-4 offset-sm-1"></div>
        </div>
      </div>

      <div class='row' style='margin: 0'>
        <div class='thumbnailView isHidden col-12' style='text-align: center'></div>

      </div>
    </div>

      <script src="https://maps.googleapis.com/maps/api/js?key=YOURGOOGLEAPIKEY&callback=initMap&v=weekly&libraries=marker" defer></script>
  
      </body>

</html>

