<?php 

$fileName = $_GET['filename'];

$file = fopen($fileName, "rb");
$line = fread($file,16);
$signatureArray = unpack ("H*", $line);
fclose($file);

$signature="";
foreach ($signatureArray as $key => $value) {
    $signature = $signature . $value;
}

$signature = strtoupper($signature);

echo $signature;




