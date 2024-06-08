<?php
include ("../endianBinaryReader.php");
include ("../EXIFItem.php");
include("EXIFTags.php");

$fileName = $_POST['filename'];
$endianType = $_POST['endian'];
$EXIFData = [];


if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}
    // For standard IFD0, EXIF images etc
    // Filename, filesize, MIME type
    // Find IFD0  Width & Height are Tags 0x100 and 0x101 in IFD0
    $jpegHeaderSize = 0x0C;
      $file = fopen($fileName, "rb") or die("Unable to open file");
      // check endianness
      fseek ($file, $jpegHeaderSize);
      $endianCheck = exif_readUINT16($file, $endian);

      if ($endianCheck==0x4D4D) {
          $endian = true;
      } else {
          $endian = false;
      }

        // Get IFD0 pointer     
        fseek($file,4+$jpegHeaderSize);
      
        $IFD0 = exif_readUINT32($file,$endian)+$jpegHeaderSize;
       
        fseek($file, $IFD0);
 

        $blocks = getTagsToArray($file, $endian,0, $jpegHeaderSize);

        $imageWidth = 0;
        $imageHeight = 0;
        $make = "";
        $model = "";
        $artist = "";
        $copyright = "";
        $EXIFPointer = 0;
        $GPSPointer = 0;

        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x100: // Image Width
                    $imageWidth = $EXIFBlock->getPointer();
                    break;
                case 0x101: // Image Size
                    $imageHeight = $EXIFBlock->getPointer();
                    break;
                case 0x10F: // Make
                    $make = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $make = exif_readString($file, $make, $EXIFBlock->getSize());
                    break;
                case 0x110: // Model
                    $model = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $model = exif_readString($file, $model, $EXIFBlock->getSize());
                    break;
                case 0x13B: // Artist
                    $artist = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    if ($artist=="0") {
                        $artist="";
                    } else {
                        $artist = exif_readString($file, $artist, $EXIFBlock->getSize());
                    }
                    break;
                case 0x8298: // Copyright
                    $copyright = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    if ($copyright=="0") {
                        $copyright="";
                    } else {
                        $copyright = exif_readString($file, $copyright, $EXIFBlock->getSize());
                    }
                    break;
                case 0x8769: // EXIF Pointer
                    $EXIFPointer = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    break;
                case 0x8825: // GPS Pointer
                    $GPSPointer = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    break;
                }
            }

        // From EXIF Block
        fseek($file, $EXIFPointer);
        $blocks = getTagsToArray($file, $endian);
        $shutterSpeed = '';
        $exposureTime = 0;
        $createDate = '';
        $fnumber = "";
        $iso = "";
        $focalLength = "";
        $focalLength35 = "";
        $flash = "";
        $lens = "not recorded in EXIF";
        $comment = "";

        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x829A: // Exposure time
                    $shutterSpeed = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $shutterSpeed = exif_readRational($file, $shutterSpeed,1, $endian);
                    $exposureTime = $shutterSpeed[0];
                    $shutterSpeed = ToFraction($exposureTime) ." sec";
                    if ($exposureTime>1) {
                        $shutterSpeed .= "s";
                    }
                    break;
                case 0x829D: // Exposure time
                    $fnumber = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $fnumber = exif_readRational($file, $fnumber,1, $endian);
                    break;
                case 0x8827: // ISO
                    $iso = $EXIFBlock->getPointer();
                    if ($iso>65535) {
                        $iso = $iso >> 16;
                    }
                    $iso = "ISO-" . $iso; 
                    break;
                case 0x9004: // Creation date
                    $createDate = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $createDate = exif_readString($file, $createDate, $EXIFBlock->getSize());
                    $createDate = substr($createDate,8,2) . "/" .substr($createDate,5,2) . "/" .substr($createDate,0,4) ." at ". substr($createDate,11,8);
                    break;
                case 0x9209: // Flash
                    $flash = $EXIFBlock->getPointer();
                    if ($flash>65535) {
                        $flash = $flash >> 16;
                    }
                    $flash = $ENUM_flashType[$flash];
                    break;
                case 0x920A: // Focal length
                    $focalLength = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $focalLength = exif_readRational($file, $focalLength,1,$endian);
                    $focalLength = $focalLength[0] . "mm";
                    break;
                case 0x9286: // user comment
                    $comment = $EXIFBlock->getPointer() + $jpegHeaderSize;     
                    if ($comment=="0") {
                        $comment = "";
                    } else {
                        $comment = exif_readString($file, $comment, $EXIFBlock->getSize());
                    }
                    break;
                case 0xA405: // Focal length
                    $focalLength35 = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $focalLength35 = exif_readRational($file, $focalLength[0],1,$endian);
                    if ($focalLength35) {
                        $focalLength35 = $focalLength35[0] . "mm";
                    } else {
                        $focalLength35 = "";
                    }
                    break;
                case 0xA434: // Lens model
                    $lens = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $lens = exif_readString($file, $lens, $EXIFBlock->getSize());
                    break;

            }
        }   
            
// From GPS Block
$GPSExists = false;
if ($GPSPointer!=0) {
    fseek($file, $GPSPointer);
    $blocks = getTagsToArray($file, $endian);
    
    $latDirection = "";
    $longDirection = "";
    $latCoord = "";
    $longCoord = "";
    $altDirection = "";
    $altCoord = "";
    $altitude = "";
    if (count($blocks)!=1) { 
        $GPSExists = true;
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x0001: // Latitude Direction
                    $latDirection = $EXIFBlock->getPointer();
                    $latDirection = exif_readIString($latDirection,$endian,1);
                    break;
                case 0x0002: // Latitude Coordinate Rationals
                    $latCoord = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $latCoord = exif_readRational($file,$latCoord,3,$endian);
                case 0x0003: // Longitude Direction
                    $longDirection = $EXIFBlock->getPointer();
                    $longDirection = exif_readIString($longDirection,$endian,1);
                    break;
                case 0x0004: // Latitude Coordinate Rationals
                    $longCoord = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $longCoord = exif_readRational($file,$longCoord,3,$endian);
                    break;
                case 0x0005: // Altitude reference
                    $altDirection = $EXIFBlock->getPointer();
                    break;
                case 0x0006: // Altitude in metres
                    $altCoord = $EXIFBlock->getPointer() + $jpegHeaderSize;
                    $altCoord = exif_readRational($file, $altCoord,1, $endian);
                }
        }   
    }
}
        fclose($file);   
        $EXIFData = array("Width"=>$imageWidth, "Height"=>$imageHeight, "MIME Type"=>mime_content_type($fileName),"Make"=>$make, "Model"=>$model);
        $EXIFData += ["Artist"=>$artist, "Copyright"=>$copyright, "Create Date"=>$createDate, "Comment"=>$comment];
        $EXIFData += ["Exposure Time"=>$shutterSpeed, "Aperture"=>$fnumber, "ISO"=>$iso, "Focal Length"=>$focalLength, "Focal Length 35"=>$focalLength35, "Flash"=>$flash, "Lens Model"=>$lens];
        if($GPSExists) {$EXIFData += ["GPSExists"=>$GPSExists, "Latitude Dir"=>$latDirection, "Longitude Dir"=>$longDirection, "Altitude Dir"=>$altDirection, "Lat Coords"=>$latCoord, "Long Coords"=>$longCoord, "Altitude Coords"=>$altCoord];}
        $EXIFData +=['filename'=>$fileName];
        echo json_encode($EXIFData);

       