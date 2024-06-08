<?php
include ("../endianBinaryReader.php");
include ("../EXIFItem.php");

$fileName = $_POST['filename'];
$endianType = $_POST['endian'];
if ($endianType=='big') {
    $endian=true;
} elseif ($endianType=='little') {
    $endian = false;
}
    // Find IFD0 Tag 0x111 and length in Tag 0x117
      $file = fopen($fileName, "rb") or die("Unable to open file");
        // Get IFD0 pointer     
        fseek($file,4);
        $IFD0 = exif_readUINT32($file,$endian);
        fseek($file, $IFD0);
       
        $blocks = getTagsToArray($file, $endian);
        $thumbStart = 0;
        $thumbSize = 0;
            
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x111: // Image Start
                    $thumbStart = $EXIFBlock->getPointer();
                    break;
                case 0x117: // Image Size
                    $thumbSize = $EXIFBlock->getPointer();
                    break;
                }
                if ($thumbStart!=0 && $thumbSize!=0) break;
            }

            
            $thumbArray = getImageArray($fileName, $thumbStart, $thumbSize);
            $fileThumbName = substr($fileName,0,strlen($fileName)-4) . "-full.jpg";
            
            $thumbBlob = pack('C*', ...$thumbArray);
            file_put_contents($fileThumbName, $thumbBlob);
    
        unset ($thumbBlob);
        fclose($file);   
        
        echo $fileThumbName;

            // Delete old thumbnail.jpg
            //   unlink ("uploads/thumbnail.jpg");
        