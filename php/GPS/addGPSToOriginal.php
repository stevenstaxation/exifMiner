<?php

include ("../endianBinaryReader.php");
include ("../EXIFItem.php");
include("../EXIF/EXIFTags.php");

$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$altitude = $_POST['altitude'];
$datestamp = $_POST['datestamp'];
$timestamp = $_POST['timestamp'];
$include_alt = $_POST['include_altitude'];
$include_date = $_POST['include_date'];
$include_time = $_POST['include_time'];
$fileName = $_POST['filename'];
$endianType = $_POST['endian'];

if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}

// find current GPS pointer tag 0x8825
// if it doesn't exist extend IFD3 (i.e. add IFD4 to end of file) and add tag
// if exists extend group 0x8825 block) to point to end of file
// add new tags

$EXIFData = [];
$blocks = [];
      
$file = fopen($fileName, "c+b") or die("Unable to open file");
// Get IFD0 pointer     
fseek($file,4);
$IFD0 = exif_readUINT32($file,$endian);
fseek($file, $IFD0);  
$blocks = getTagsToArray($file, $endian);

$GPSPointer = 0;

foreach ($blocks as $EXIFBlock) {
    switch ($EXIFBlock->getTag()) {
        case 0x8825:
            $GPSPointer = $EXIFBlock->getPointer();
            break;
    }
}


$IFDEnd = ftell($file) - 4;  

fseek($file, filesize($fileName));
$endOfFile = ftell($file);
$ArrayUInt32 = getArrayUInt32($endian, $endOfFile);

if (!$GPSPointer) {
    fseek($file,$IFDEnd);
    fwrite($file, pack('c*', $ArrayUInt32[0], $ArrayUInt32[1], $ArrayUInt32[2], $ArrayUInt32[3]));      
    // add tag size 1, add GPS tag, and point it to 'new' end of file
      $ArrayForGPS = [];
       fseek($file, $endOfFile);
       array_push($ArrayForGPS, ...getArrayUInt16($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,0x8825));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,4));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,$endOfFile+14));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,5));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,3));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,1));
       
       foreach ($ArrayForGPS as $GPSItem) {
           fwrite($file, pack('c*', $GPSItem));      
       }
 } else {
    fseek($file, $GPSPointer);
    $numCurrentTags = exif_readUINT16($file, $endian);
    fseek($file, $numCurrentTags*12,SEEK_CUR);
    fwrite($file, pack('c*', $ArrayUInt32[0], $ArrayUInt32[1], $ArrayUInt32[2], $ArrayUInt32[3]));      
    $ArrayForGPS = [];
       fseek($file, $endOfFile);
       array_push($ArrayForGPS, ...getArrayUInt16($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,5));
       array_push($ArrayForGPS, ...getArrayUInt16($endian,3));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,1));
       array_push($ArrayForGPS, ...getArrayUInt32($endian,0));
       foreach ($ArrayForGPS as $GPSItem) {
        fwrite($file, pack('c*', $GPSItem));      
    }
    }    


    fclose ($file);

    

    

function getArrayUInt32($byteOrder, $value) {
    $retValue = [];

    if ($byteOrder==true) {
        $retValue[0] = ($value & 0xFF000000) >> 24;
        $retValue[1] = ($value & 0xFF0000) >> 16;
        $retValue[2] = ($value & 0xFF00) >> 8;
        $retValue[3] = ($value & 0xFF);
    } else {
        $retValue[3] = ($value & 0xFF000000) >> 24;
        $retValue[2] = ($value & 0xFF0000) >> 16;
        $retValue[1] = ($value & 0xFF00) >> 8;
        $retValue[0] = ($value & 0xFF);
    }
    return $retValue;
}
function getArrayUInt16($byteOrder, $value) {
    $retValue = [];

    if ($byteOrder==true) {
        $retValue[0] = ($value & 0xFF00) >> 8;
        $retValue[1] = ($value & 0xFF);
    } else {
        $retValue[1] = ($value & 0xFF00) >> 8;
        $retValue[0] = ($value & 0xFF);
    }
    return $retValue;
}

