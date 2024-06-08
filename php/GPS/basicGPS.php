<?php
include ("../endianBinaryReader.php");
include ("../EXIFItem.php");
include("../EXIF/EXIFTags.php");

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

      $file = fopen($fileName, "rb") or die("Unable to open file");
        // Get IFD0 pointer     
        fseek($file,4);
        $IFD0 = exif_readUINT32($file,$endian);
       
        fseek($file, $IFD0);
      
        $blocks = getTagsToArray($file, $endian);

        $EXIFPointer = 0;
        $GPSPointer = 0;

        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x8825: // GPS Pointer
                $GPSPointer = $EXIFBlock->getPointer();
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
    $latCoord = 0;
    $longCoord = 0;
    $altDirection = "";
    $altCoord = 0;
    $altitude = "";
    $timeStamp =[0,0,0];
    $dateStamp = "";

    if (count($blocks)!=1) { 
        $GPSExists = true;
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x0001: // Latitude Direction
                    $latDirection = $EXIFBlock->getPointer();
                    $latDirection = exif_readIString($latDirection,$endian,1);
                    break;
                case 0x0002: // Latitude Coordinate Rationals
                    $latCoord = $EXIFBlock->getPointer();
                    $latCoord = exif_readRational($file,$latCoord,3,$endian);
                case 0x0003: // Longitude Direction
                    $longDirection = $EXIFBlock->getPointer();
                    $longDirection = exif_readIString($longDirection,$endian,1);
                    break;
                case 0x0004: // Latitude Coordinate Rationals
                    $longCoord = $EXIFBlock->getPointer();
                    $longCoord = exif_readRational($file,$longCoord,3,$endian);
                    break;
                case 0x0005: // Altitude reference
                    $altDirection = $EXIFBlock->getPointer();
                    break;
                case 0x0006: // Altitude in metres
                    $altCoord = $EXIFBlock->getPointer();
                    $altCoord = exif_readRational($file, $altCoord,1, $endian);
                    break;
                case 0x0007: // Timestamp
                    $timeStamp = exif_readRational($file, $altCoord,3, $endian);
                    break;
                case 0x001D: // Date stamp
                    $dateStamp = exif_readString($file, $EXIFBlock->getPointer(), $EXIFBlock->getSize());
                    break;
                }

        }   
    }
}

        fclose($file);   
        if ($GPSExists) {
            $latCoord = $latCoord[0] + $latCoord[1]/60 + $latCoord[2]/3600;
            $longCoord = $longCoord[0] + $longCoord[1]/60 + $longCoord[2]/3600;
            
            if ($latDirection=="S") {$latCoord = -$latCoord;}
            if ($longDirection=="W") {$longCoord = -$longCoord;}
            if ($altDirection=="1") {$altCoord = -$altCoord;}
            $timeStamp = $timeStamp[0] . ":" . $timeStamp[1] . ":" . $timeStamp[2];
            $EXIFData = ["Lat Coords"=>$latCoord, "Long Coords"=>$longCoord, "Altitude Coords"=>$altCoord];
            $EXIFData +=["Time Stamp"=>$timeStamp, "Date Stamp"=>$dateStamp];
        }
        echo json_encode($EXIFData);

       