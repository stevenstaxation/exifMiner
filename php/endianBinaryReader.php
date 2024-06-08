<?php
// ********************************
// ENDIAN BINARY READER FUNCTIONS *
// ********************************

function exif_readUINT16($Handle, $BigEndian = false, $size = 1, $pointer = 0) {
 if ($pointer!=0) { fseek($Handle, $pointer);}

    if ($size ==1) {
        if ($BigEndian == false) {
                $byte0 = unpack("v*",fread($Handle,2));
        } else {
                $byte0 = unpack("n*",fread($Handle,2));  
        }
            return $byte0[1];
        }
    $retValue = [];
    for ($ix=0; $ix<$size; $ix++) {
        if ($BigEndian == false) {
            $byte0 = unpack("v*",fread($Handle,2));
        } else {
            $byte0 = unpack("n*",fread($Handle,2));  
        }
        array_push($retValue, $byte0[1]);
    }

    return $retValue;
}

function exif_readSINT16($Handle, $BigEndian = false, $size = 1, $pointer = 0) {
 
    if ($BigEndian == false) {
            $byte0 = unpack("s*",fread($Handle,2));
    } else {
            $byte0 = unpack("s*",fread($Handle,2));  
    }
    
     return $byte0[1];
}

function exif_readUINT32($Handle, $BigEndian = false) {
 
    if ($BigEndian == false) {
            $byte0 = unpack("V*",fread($Handle,4));
    } else {
            $byte0 = unpack("N*",fread($Handle,4));  
    }
    
     return $byte0[1];
}

function exif_readSINT32($Handle, $BigEndian = false) {
 
    if ($BigEndian == false) {
            $byte1 = unpack("l*",fread($Handle,4));
    } else {
            $byte0 = unpack("l*",fread($Handle,4));  
            $byte1 = switchEndian($byte0[1]);
    }
    
     return $byte1[1];
}

// read string pointed to by $Offset
function exif_readString($Handle, $Offset, $Length) {
    fseek($Handle, $Offset);
    $byte0 = unpack("A*", fread($Handle,$Length));
    $byte0[1] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $byte0[1]);
    return $byte0[1];    
}

// read string (less than 4 bytes) within Pointer
function exif_readIString($Pointer, $BigEndian = false, $stringSize = 4) {
    if ($BigEndian != true) {
        $PointerA = switchEndian($Pointer);
        $Pointer = $PointerA[1];
    }
            
    $byte[0] = intval($Pointer / (256*256*256));
    $byte[1] = intval(($Pointer - $byte[0]*256*256*256)/(256*256));
    $byte[2] = intval((($Pointer/(256*256)) - intval($Pointer/(256*256))) * 256);
    $byte[3] = (($Pointer/256) - intval($Pointer/256)) *256;  
        
    $retVal = "";
    for ($b=0; $b<$stringSize; $b++) {
        if ($byte[$b] != 0) {
            $retVal = $retVal . chr($byte[$b]);
        }
    }
  //  $retVal = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $retVal);
    return $retVal;
}

function exif_readRational($Handle, $Offset, $Count, $BigEndian = false) {
    fseek($Handle, $Offset);
    $rationals = array();
    
    for ($r = 0 ; $r < $Count; $r++) {
  
        if ($BigEndian == false) {
            $numerator = unpack("V*", fread($Handle,4));
            $denominator = unpack("V*", fread($Handle,4)); 
        } else {
            $numerator = unpack("N*", fread($Handle,4));
            $denominator = unpack("N*", fread($Handle,4));           
        }
        
        if ($denominator[1]!=0) {
            array_push($rationals,floatval($numerator[1]/$denominator[1])); 
        } else {
            array_push($rationals,floatval(0));         
        }

    }

    return $rationals;
    
}

function exif_readSRational($Handle, $Offset, $Count, $BigEndian = false) {
    fseek($Handle, $Offset);
    $rationals = array();
    
    for ($r = 0 ; $r < $Count; $r++) {
  
        if ($BigEndian == false) {
            $numerator = unpack("l*", fread($Handle,4));
            $denominator = unpack("l*", fread($Handle,4)); 
        } else {
            $numerator = unpack("l*", fread($Handle,4));
            $denominator = unpack("l*", fread($Handle,4));           
        }

        if ($denominator[1]!=0) {
            array_push($rationals,floatval($numerator[1]/$denominator[1])); 
        } else {
            array_push($rationals,floatval(0));         
        }
    }

    return $rationals;
    
}

function ToFraction($Decimal) {
    
    if ($Decimal < 0 || !is_numeric($Decimal)) {
        // Negative digits need to be passed in as positive numbers
        // and prefixed as negative once the response is imploded.
        return false;
    }
    
    if ($Decimal == 0) {
        return 0;
    }
    
    $tolerance = 1.e-2;

    $numerator = 1;
    $h2 = 0;
    $denominator = 0;
    $k2 = 1;
    $b = 1 / $Decimal;
    do {
        $b = 1 / $b;
        $a = floor($b);
        $aux = $numerator;
        $numerator = $a * $numerator + $h2;
        $h2 = $aux;
        $aux = $denominator;
        $denominator = $a * $denominator + $k2;
        $k2 = $aux;
        $b = $b - $a;
    } while (abs($Decimal - $numerator / $denominator) > $Decimal * $tolerance);
    
    if ($denominator == 1) {
        return $numerator;
    } elseif ($numerator < $denominator) {
        return $numerator ."/" .$denominator;
    } else {
        return $numerator/$denominator;
    }
}

function ToDecimal($Fraction, $decimals = 2) {
   $Fractions= explode(" ",rtrim($Fraction));
   $retFraction ="";
    
    foreach ($Fractions as $Fract) {
        $nominator = explode("/", $Fract);
        if (!is_numeric($nominator[0]) || !is_numeric($nominator[1])) {
            $retFraction = $retFraction . "0, ";
        } else {
            $retFraction = $retFraction . round($nominator[0] / $nominator[1], $decimals) . ", ";
        }
    }
    return rtrim($retFraction,", ");
}

function switchEndian($UINT32, $sFormat='L', $dFormat='N') {
    $UINT32 = intval($UINT32, 16);
    $UINT32 = pack($sFormat, $UINT32);
    $UINT32 = unpack($dFormat, $UINT32);
    return $UINT32;
}

function getTagsToArray($file, $BE, $reportGroup = 0, $JPEGOffset=0) {
    $blocks = array();
    $newIFD = ftell($file);
    while ($newIFD-$JPEGOffset != 0) {
        fseek($file, $newIFD);
         $IFDSize = exif_readUINT16($file,$BE);
     
        // read IFD into $blocks array of EXIFItem  
        for ($ix=0; $ix < $IFDSize; $ix++) {
            $thisTag = exif_readUINT16($file,$BE);
            $thisType = exif_readUINT16($file,$BE);
            $thisSize = exif_readUINT32($file,$BE);
            $thisPointer = exif_readUINT32($file,$BE); 
            if ($thisType==3 && $thisPointer>65535 && $thisSize==1) {$thisPointer = $thisPointer >> 16;}
            $thisGroup = $reportGroup;    
            $block = new EXIFItem($thisTag, $thisType, $thisSize, $thisPointer, $thisGroup);
            array_push ($blocks, $block);    
        }
        // are there any more?
        $newIFD = exif_readUINT32($file, $BE) + $JPEGOffset;
  
        $reportGroup++;
    }
    return $blocks;  
}

function getImageArray($imageFile, $blockStart, $blockSize) {
    $handle = fopen($imageFile,"rb");
    fseek($handle,$blockStart, SEEK_SET);
    $contents = fread($handle, $blockSize);
    $byteArray = unpack("C*", $contents);
    fclose($handle);
    return $byteArray;
}
// function OrientImage($imageName) {
//     $angle = 0;
//     switch ($_SESSION['Orientation']) {
//         case 3:
//             $angle = 180;
//             break;
//         case 5:
//             $angle = 90;
//             break;
//         case 6:
//             $angle = 270;
//             break;
//         case 7:
//             $angle = 270;
//             break;
//         case 8:
//             $angle = 90;
//             break;
//         default:
//             break;
//     }
    
//    return $angle;
    
// }



