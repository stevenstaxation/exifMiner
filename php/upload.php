<?php
session_start();

$fileName = $_FILES["customFile"]["name"]; // The file name
$fileTmpLoc = $_FILES["customFile"]["tmp_name"]; // File in the PHP tmp folder
$fileType = $_FILES["customFile"]["type"]; // The type of file it is
$fileSize = $_FILES["customFile"]["size"]; // File size in bytes
$fileErrorMsg = $_FILES["customFile"]["error"]; // 0 for false... and 1 for true
if (!$fileTmpLoc) { // if file not chosen
    echo "ERROR: Please browse for a file before clicking the upload button.";
    exit();
}
if (move_uploaded_file($fileTmpLoc, "../uploads/$fileName")) {
} else {
    echo "upload failed";
}

