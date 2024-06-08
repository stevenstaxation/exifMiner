<?php
include ("../endianBinaryReader.php");


$fileName = $_POST['filename'];

if (substr($fileName,-3)=="CR2") {
    $fileJPEGName = substr($fileName,0,strlen($fileName)-4) . "-full.jpg";
} else {
    $fileJPEGName = $fileName;
}

$endianType = $_POST['endian'];

if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}

$fileGDI = imagecreatefromjpeg($fileJPEGName);

$width = imagesx($fileGDI);
$height = imagesy($fileGDI);

$reds = array_fill(0,256,0);
$greens = array_fill(0,256,0);
$blues = array_fill(0,256,0);
$lumins = array_fill(0,256,0);
$luminance = 0;

for($x = 0; $x < $width; $x++) {
    for($y = 0; $y < $height; $y++) {
        $color_index = imagecolorat($fileGDI, $x, $y);
        $color_tran = imagecolorsforindex($fileGDI, $color_index);
        $reds[$color_tran['red']]++;
        $greens[$color_tran['green']]++;
        $blues[$color_tran['blue']]++;
        $luminance = $color_tran['red'] * 0.212655;
        $luminance += $color_tran['green'] * 0.715158;
        $luminance += $color_tran['blue'] * 0.072187;
        $lumins[round($luminance)]++;
        
    }
}
imagedestroy($fileGDI);
$RGB = array($reds,$greens, $blues, $lumins);
echo json_encode($RGB);


