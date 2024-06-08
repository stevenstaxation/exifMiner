<?php
include ("../endianBinaryReader.php");


$fileName = $_POST['filename'];
$fileJPEGName = substr($fileName,0,strlen($fileName)-4) . "-full.jpg";
$endianType = $_POST['endian'];

if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}


$fileGDI = imagecreatefromjpeg($fileJPEGName);

$width = imagesx($fileGDI);
$height = imagesy($fileGDI);

$blacks = array();
array_push($blacks, [$width, $height]);

for($x = 0; $x < $width; $x++) {
    for($y = 0; $y < $height; $y++) {
        $color_index = imagecolorat($fileGDI, $x, $y);
        $color_tran = imagecolorsforindex($fileGDI, $color_index);
        if($color_tran['red']==0 && $color_tran['green']==0 && $color_tran['blue']==0) {
           array_push($blacks,[$x, $y]);
        }
    }
}
imagedestroy($fileGDI);

echo json_encode($blacks);

