<nav class="navbar navbar-expand-md bg-info navbar-dark">
    <div class='container-fluid'>
        <a id='navBrand' class="navbar-brand" href="#">EXIF Miner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavBar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="collapsibleNavBar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item ui-corner-all"><a class="nav-link" href="#" id="showUploader" style='padding-left:25px;'>Hide Uploader</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown ui-corner-all"><a class="nav-link dropdown-toggle" href="#" id="imageDrop" data-bs-toggle="dropdown">View</a>
                    <div class="dropdown-menu viewMenu">
                        <a id='showImage' class="dropdown-item viewMenu disabled" href="#">Large Image</a>
                        <!-- <a id='showThumbnail' class="dropdown-item viewMenu  disabled" href="#">Thumbnail</a> -->
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item viewMenu disabled" href="#" id="showAFPoints">All AF Points</a>
                        <a class="dropdown-item viewMenu disabled" href="#" id="showSelectedAFPoints">Selected AF Points</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item viewMenu disabled" href="#" id="showHighlights">Highlights Warning</a>
                        <a class="dropdown-item viewMenu disabled" href="#" id="showShadows">Shadows Warning</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item viewMenu disabled" href="#" id="R3Grid">Rule of Thirds Grid</a>
                        <a class="dropdown-item viewMenu disabled" href="#" id="Diagonals">Diagonal Grid</a>
                        <a class="dropdown-item viewMenu disabled" href="#" id="PhiGrid">Phi Grid</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item viewMenu disabled" href="#" id="showMap">Show Map</a>
        
                    </div>
                </li>
                <li class="nav-item dropdown ui-corner-all">
                    <a class="nav-link dropdown-toggle" href="#" id="imageDrop" data-bs-toggle="dropdown">Image</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" id="download">Print Report</a>
                        <a class="dropdown-item" href="#" id="rotate-90">Download Tags</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Download Image</a>
                    </div>
                </li>
                <li class="nav-item dropdown ui-corner-all"><a class="nav-link dropdown-toggle" href="#" id="exifDrop" data-bs-toggle="dropdown">Metadata</a>
                    <div class="dropdown-menu">
                        <a id='showAllTags' class="dropdown-item viewMenu disabled" href="#">Show All</a>
                        <!-- <a id='showBasicTags' class="dropdown-item viewMenu disabled" href="#">Basic</a> -->
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item viewMenu disabled" href="#" id="editTags">Edit</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" id="geoTagImage">Geo Tag</a>
                    </div>
                </li>
               
                <li class="nav-item ui-corner-all"><a class="nav-link" id="aboutDrop">About</a>
                </li>
            </ul>
        </div>
    </div>
</nav>