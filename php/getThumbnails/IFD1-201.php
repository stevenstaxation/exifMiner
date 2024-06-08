<?php
include ("../endianBinaryReader.php");
include ("../EXIFItem.php");

// Canon RAW Image (CR2)
// Pentax RAW Image
// Sony RAW Image
   
// Find IFD0, at end move to IFD1
// IFD1 will contain pointer to JPEG in Tag 0x201 and length in Tag 0x202
$fileName = $_POST['filename'];
$endianType = $_POST['endian'];
if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}

$file = fopen($fileName, "rb") or die("Unable to open file");
    // Get IFD0 pointer     
    fseek($file,4);
    $IFD0 = exif_readUINT32($file,$endian);

    fseek($file, $IFD0);
       
        $IFDSize = exif_readUINT16($file,$endian);

        fseek($file, $IFDSize*12, SEEK_CUR);
        $IFD1 = 0;
        $IFD1 = exif_readUINT32($file, $endian);
       
        if ($IFD1 != 0) {
            fseek($file, $IFD1);
            $blocks = getTagsToArray($file, $endian);
            $thumbStart = 0;
            $thumbSize = 0;
            
            foreach ($blocks as $EXIFBlock) {
                switch ($EXIFBlock->getTag()) {
                    case 0x201: // Thumb Start
                        $thumbStart = $EXIFBlock->getPointer();
                        break;
                    case 0x202: // Thumb Size
                        $thumbSize = $EXIFBlock->getPointer();
                        break;
                }
            }
            
            $thumbArray = getImageArray($fileName, $thumbStart, $thumbSize);
            $fileThumbName = substr($fileName,0,strlen($fileName)-4) . "-thumb.jpg";
            
            $thumbBlob = pack('C*', ...$thumbArray);
            file_put_contents($fileThumbName, $thumbBlob);
    
            // $thumbWidth = 240;
            // $thumbHeight = 180; 
            // $rAngle = OrientImage($fileName);
        
            // if ($rAngle ==90 || $rAngle == 270) {
            //     $temp = $thumbWidth;
            //     $thumbWidth = $thumbHeight;
            //     $thumbHeight = $temp;
            // }  
            fclose($file); 
            echo $fileThumbName;

            // Delete old thumbnail.jpg
            //   unlink ("uploads/thumbnail.jpg");
        }
   
        