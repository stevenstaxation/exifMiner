<?php
include ("../endianBinaryReader.php");
include ("../EXIFItem.php");
include("EXIFTags.php");

$fileName = $_POST['filename'];
$endianType = $_POST['endian'];
$jpegOffset = $_POST['jpegOffset'];
if ($jpegOffset==0) {return;}
if ($jpegOffset<=4) {$jpegOffset = 0xC;} else {$jpegOffset = 0;}

$EXIFData = [];


if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}
    
      $file = fopen($fileName, "rb") or die("Unable to open file");
        // Get IFD0 pointer     
        fseek($file,4 + $jpegOffset);
        $IFD0 = exif_readUINT32($file,$endian);
        fseek($file, $IFD0 + $jpegOffset);
        $blocks = getTagsToArray($file, $endian,0, $jpegOffset);
        $EXIFPointer = 0;
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x8769: // EXIF pointer
                    $EXIFPointer = $EXIFBlock->getPointer();
                    break;
            }
        }

        fseek($file, $EXIFPointer + $jpegOffset);
        $blocks = getTagsToArray($file, $endian, 0, $jpegOffset);
        $makernotePointer = 0;
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x927C: // Makernote pointer
                    $makernotePointer = $EXIFBlock->getPointer();
                    break;
            }
        }   
        if ($jpegOffset!=0) {return;}

        fseek($file, $makernotePointer + $jpegOffset);
        $blocks = getTagsToArray($file, $endian, 0, $jpegOffset);
        $AFInfo = 0;
        $blockType = 0;
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x12: // AFInfo block
                    $AFInfo = $EXIFBlock->getPointer() + $jpegOffset;
                    $blockType = 1;
                    break;
                case 0x26: // AFInfo2 block
                    $AFInfo = $EXIFBlock->getPointer() + $jpegOffset;
                    $blockType = 2;
                    break;
                case 0x3C: // AFInfo3 block
                    $AFInfo = $EXIFBlock->getPointer() + $jpegOffset;
                    $blockType = 3;
                    break;
        
            }
        }   
        // var_dump($AFInfo);
        $AFBlockSize = 0;
        $AFAreaMode = 0;
        $AFPoints = 0;
        $ValidAFPoints = 0;
        $AFSize = [0,0];
        $AFAreaWidths = [];
        $AFAreaHeights = [];
        $AFXPositions = [];
        $AFYPositions = [];
        $AFInFocus = 0;
        $AFSelected = 0;
        $AFPrimary = 0;

        switch ($blockType) {
            case 1:
                fseek($file, $AFInfo);
                $AFPoints = exif_readUINT16($file, $endian);
                $ValidAFPoints = exif_readUINT16($file, $endian);
                exif_readUINT16($file, $endian); // canon width
                exif_readUINT16($file, $endian); // canon height
                $AFSize[0] = exif_readUINT16($file, $endian); // AF width
                $AFSize[1] = exif_readUINT16($file, $endian); // AF width
                $AFAreaWidths[0] = exif_readUINT16($file, $endian);
                $AFAreaHeights[0] = exif_readUINT16($file, $endian);
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFXPositions[$ix] = exif_readSINT16($file, $endian);
                }
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFYPositions[$ix] = exif_readSINT16($file, $endian);
                }  
                for ($ix=1; $ix<$AFPoints; $ix++) {
                    $AFAreaWidths[$ix] = $AFAreaWidths[0];
                    $AFAreaHeights[$ix] = $AFAreaHeights[0];
                }

                $AFInFocus = exif_readUINT16($file, $endian);
                $AFSelected = exif_readUINT16($file, $endian);
                $AFPrimary = exif_readUINT16($file, $endian);
                break;
            case 2:
                fseek($file, $AFInfo);
                $AFBlockSize = exif_readUINT16($file, $endian);
                $AFAreaMode = exif_readUINT16($file, $endian); // area mode
                $AFPoints = exif_readUINT16($file, $endian);
                $ValidAFPoints = exif_readUINT16($file, $endian);
                exif_readUINT16($file, $endian); // canon width
                exif_readUINT16($file, $endian); // canon height
                $AFSize[0] = exif_readUINT16($file, $endian); // AF width
                $AFSize[1] = exif_readUINT16($file, $endian); // AF width
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFAreaWidths[$ix] = exif_readUINT16($file, $endian);
                }
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFAreaHeights[$ix] = exif_readUINT16($file, $endian);
                }
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFXPositions[$ix] = exif_readSINT16($file, $endian);
                }
                for ($ix=0; $ix<$AFPoints; $ix++) {
                    $AFYPositions[$ix] = exif_readSINT16($file, $endian);
                }  
                $AFInFocus = exif_readUINT32($file, $endian);
                $AFSelected = exif_readUINT32($file, $endian);
                $AFPrimary = exif_readUINT32($file, $endian);
                break;
            case 3:
                break;
        } 

        fclose($file);   
        $EXIFData = array("ValidPoints"=>$ValidAFPoints, "AFSize"=>$AFSize,"Widths"=>$AFAreaWidths,"Heights"=>$AFAreaHeights,"X"=>$AFXPositions,"Y"=>$AFYPositions);
        $EXIFData += ["Focussed"=>$AFInFocus, "Selected"=>$AFSelected, "Primary"=>$AFPrimary, "Area Mode"=>$AFAreaMode];
        echo json_encode($EXIFData);

       