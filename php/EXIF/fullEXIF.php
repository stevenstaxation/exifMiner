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
$blocks = [];
$blocks2=[];

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
        fseek($file,4 + $jpegOffset);
        $IFD0 = exif_readUINT32($file,$endian) + $jpegOffset;
        fseek($file, $IFD0);  
        $blocks = getTagsToArray($file, $endian, 0, $jpegOffset);
        $EXIFPointer = 0;
        $XMPPointer = 0;
        $GPSPointer = 0;
        $MakernotePointer = 0;

        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x02BC:
                    $XMPPointer = $EXIFBlock->getPointer() + $jpegOffset;
                    break; 
                case 0x8769:
                    $EXIFPointer = $EXIFBlock->getPointer() + $jpegOffset;
                    break;
                case 0x8825:
                    $GPSPointer = $EXIFBlock->getPointer() + $jpegOffset;
                    break;
            }
        }

        fseek($file, $EXIFPointer);
        $blocks2 = getTagsToArray($file, $endian, 4);
        $blocks = array_merge($blocks, $blocks2);
        
        foreach ($blocks as $EXIFBlock) {
            switch ($EXIFBlock->getTag()) {
                case 0x927C:
                    $MakernotePointer = $EXIFBlock->getPointer() + $jpegOffset;
                    break;
                }
            }


foreach ($blocks as $EXIFBlock) {
    $EXIFItem =[];
    switch ($EXIFBlock->getTag()) {

        // tags where numeric data needs no additional parsing or formatting
      
        case 0x117: 
        case 0x202:
        case 0xA002:
        case 0xA003:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>number_format($EXIFBlock->getPointer()) . " (0x" .strtoupper(dechex($EXIFBlock->getPointer())). ")", "Group" => $EXIFBlock->getGroup() ];   
            break;

            // tags followed by 'px' for pixels
        case 0x100: // Image Width
        case 0x101:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>number_format($EXIFBlock->getPointer()) ."px", "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x11C:
            switch ($EXIFBlock->getPointer()) {
                case 1:
                    $PlanarConfig = "Chunky";
                    break;
                case 2:
                    $PlanarConfig = "Planar";
                default:
                    $PlanarConfig = "Not recorded";
                }
                $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$PlanarConfig, "Group" => $EXIFBlock->getGroup()];
                break;       
        case 0x8827:
        case 0x8831:
        case 0x8832:
        case 0x8833:
        case 0x8834:
        case 0x8835:
            $savedISO = $EXIFBlock->getPointer();
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>"ISO-" . $savedISO, "Group" => $EXIFBlock->getGroup()];                                  
            break;
        case 0xA402:
        case 0xA403:    
            switch ($EXIFBlock->getPointer()) {
                case 0:
                    $exposureMode = "Automatic";
                    break;
                case 1:
                    $exposureMode = "Manual";
                    break;
                case 2:
                    $exposureMode = "Automatic Bracketing";
                    break;
                default:
                    $exposureMode = "Not recorded";
                }
                $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$exposureMode, "Group" => $EXIFBlock->getGroup()];
                unset ($exposuremode);
                break;  
                
        case 0xA404:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>number_format(exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0],2) ."x", "Group" => $EXIFBlock->getGroup()];
            break;

        case 0xA407:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_EXIFGain[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0xA408:
        case 0xA40A:
            $contrast = $EXIFBlock->getPointer();
            if ($contrast ==2) {
                $contrast = "Hard";
            } elseif ($contrast==1) {
                $contrast = "Soft";
            } else {
                $contrast ="Normal";
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$contrast, "Group" => $EXIFBlock->getGroup()];
            unset($contrast);
            break;
        case 0xA409:
            $saturation = $EXIFBlock->getPointer();
            if ($saturation ==2) {
                $saturation = "High";
            } elseif ($saturation==1) {
                $saturation = "Low";
            } else {
                $saturation ="Normal";
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$saturation, "Group" => $EXIFBlock->getGroup()];
            unset($saturation);
            break;
        case 0x9211:
        case 0x9215:
        case 0xA211:
        case 0xA215:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup() ];   
            break;

        // tags with a string date
        case 0x132:
        case 0x9003:
        case 0x9004:
            $string = exif_readString($file, $EXIFBlock->getPointer() + $jpegOffset, $EXIFBlock->getSize());
            $year = substr($string,0,4);
            $month = substr($string,5,2);
            $day = substr($string,8,2);
            $time = substr($string,11,8);
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()], "Data"=>$day ."/" . $month ."/" . $year . " " . $time, "Group" => $EXIFBlock->getGroup()];
            break;
        // tags whose data is a string
        case 0x00B:
        case 0x001:
        case 0x10E:
        case 0x10F: 
        case 0x110:    
        case 0x131:
        case 0x13B:
        case 0x8298:
        case 0x8824:
        case 0x9010:
        case 0x9011:
        case 0x9012:
        case 0x9286:
        case 0xA420:
        case 0xA430:
        case 0xA431:
        case 0xA433:
        case 0xA434:
        case 0xA435:
        case 0xA436:
        case 0xA437:
        case 0xA438:
        case 0xA439:
        case 0xA43A:
        case 0xA43B:
        case 0xA43C:
        case 0xC614:
        case 0xC615:
        case 0xC62F:
        case 0xFDE8:
        case 0xFDE9:
        case 0xFDEA:
        case 0xFE4C:
        case 0xFE4D:
        case 0xFE4E:
        case 0xFE51:
        case 0xFE52:
        case 0xFE53:
        case 0xFE54:
        case 0xFE55:        
        case 0xFE56:
        case 0xFE57:
        case 0xFE58:
            if ($EXIFBlock->getSize()!=1) {
                $string = exif_readString($file, $EXIFBlock->getPointer() + $jpegOffset, $EXIFBlock->getSize());
                if ($string!="") {
                   $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                }
            }
            break;

    // tags which are short strings
        // case 0x9290:
        // case 0x9291:
        // case 0x9292:
        //     $subSec = exif_readIString($EXIFBlock->getPointer(), $endian, $EXIFBlock->getSize());
        //     $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$subSec, "Group" => $EXIFBlock->getGroup()];
        //     break;

    // tags whose data is an enumeration    
        case 0x103: // Compression
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_Compression[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x106: // Photometric Interpretation
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_PhotometricInterpretation[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
                    
        case 0x112: // Orientation
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_Orientation[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x128:  // Resolution unit
        case 0xA210:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_ResolutionUnit[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x8822: // Exposure program
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_ExposureProgram[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x8830: // Sensitivity type
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_SensitivityType[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;    
        case 0x9207: // Metering mode
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_MeteringMode[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;   
        case 0x9208: // Metering mode
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_LightSource[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;   
        case 0x9209: // Flash mode
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_flashType[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
        case 0x9217:  // sensing method
        case 0xA217:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_sensingMethod[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
        case 0xA001: // Colour space
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_ColourSpace[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
        case 0xA401: // Custom render
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_CustomRender[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
        case 0xA406: // Scene capture
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_SceneCapture[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
         
        case 0xC5E0: // CFA Pattern
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$ENUM_CFAPattern[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
            break;  
        case 0x4746:
            $rating = min($EXIFBlock->getPointer(),5);
            if ($rating<=0) {
                $rating = "No stars";
            } else {
                $rating = str_repeat("⭐️", $rating);
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$rating, "Group" => $EXIFBlock->getGroup()];
            unset($rating);
            break;
        case 0x4749:
            $rating = max(0,min($EXIFBlock->getPointer(),100));
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$rating ."%", "Group" => $EXIFBlock->getGroup()];
            unset($rating);
            break;
        

        // tags whose data is a rational 
        case 0x11A:
        case 0x11B:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x0211:
            $YCbCrCoefficients = exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,3,$endian);
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>"Red: " . $YCbCrCoefficients[0] . ", Green: " . $YCbCrCoefficients[1] . ", Blue: " . $YCbCrCoefficients[2], "Group" => $EXIFBlock->getGroup()];
            unset($YCbCrCoefficients);
            break;
        case 0x920E:
        case 0x920F:
        case 0xA20E:
        case 0xA20F:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>round(exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0],4), "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x829A: // exposure time
            $speed = exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0];
            $speed = ToFraction($speed);
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$speed . " sec", "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x829D: // f number
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>"ƒ/" . exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0], "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x9203: // brightness
            $brightness = number_format(exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0],2);
            if ($brightness>0) {
                $brightnessPrefix = "+";
                $brightness = $brightnessPrefix . $brightness; 
            } elseif ($brightness<0) {
                $brightnessPrefix = "-";
                $brightness = $brightnessPrefix . abs($brightness);      
            }elseif ($brightness==0) {
                $brightness="none";
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$brightness, "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x9204: // exposure compensation
            $expoCompensation = exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0];
            if ($expoCompensation>0) {
                $expoCompensationPrefix = "+";
                $expoCompensation = $expoCompensationPrefix . ToFraction($expoCompensation); 
            } elseif ($expoCompensation<0) {
                $expoCompensationPrefix = "-";
                $expoCompensation = $expoCompensationPrefix . ToFraction(abs($expoCompensation));      
            }elseif ($expoCompensation==0) {
                $expoCompensation="none";
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$expoCompensation, "Group" => $EXIFBlock->getGroup()];
            break;
        case 0x9206:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=> exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,1, $endian)[0] . "m", "Group" => $EXIFBlock->getGroup()];                                  
            break;   
        case 0x920A:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=> exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,1, $endian)[0] . "mm", "Group" => $EXIFBlock->getGroup()];                                  
            break; 
        case 0xA405:
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=> $EXIFBlock->getPointer() . "mm", "Group" => $EXIFBlock->getGroup()];                                  
            break;   
        case 0xA432:
            $lensInfo = exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,4, $endian);
            // first two elements of lensinfo are widest and tightest focal lengths, e.g. 24-135mm
            // if both the same the lens is fixed, so only show e.g. 50mm
            // zeroes in [0] means not recorded
            // second group of two are the max and min apertures of the lens, e.g. f/4.5-f5.6
            // if both are the same the lens is prime, so only show e.g. f/1.8
        
            $lensDescription = '';
            if (($lensInfo[0]==$lensInfo[1]) && $lensInfo[0]!=0) {
                $lensDescription .= $lensInfo[0] . "mm prime lens";
            } elseif ($lensInfo[0]<$lensInfo[1]) {
                $lensDescription .= $lensInfo[0] ."-" . $lensInfo[1] ."mm ";
            }
            if (($lensInfo[2]==$lensInfo[3]) && $lensInfo[2]!=0) {
                $lensDescription .= "ƒ/" . $lensInfo[2];
            } elseif ($lensInfo[2]<$lensInfo[3]) {
                $lensDescription .= "ƒ/" .$lensInfo[2] . "-" . $lensInfo[3];
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$lensDescription, "Group" => $EXIFBlock->getGroup()];                                  
            break;
     

         // tags whose data is a rational in APEX units pointed to by pointer
            case 0x9201: // shutter speed
                $speed = exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0];
                if ($speed>-15) {
                    $speed = 1 / (pow(2, $speed));
                    $speed = ToFraction($speed);
                    $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$speed . " sec", "Group" => $EXIFBlock->getGroup()];
                }
                break;
            case 0x9202: // aperture
            case 0x9205:
                $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>"ƒ/" . round(pow(2,(exif_readRational($file,$EXIFBlock->getPointer() + $jpegOffset,1,$endian)[0]/2)),1), "Group" => $EXIFBlock->getGroup()];
                break;

        // tags whose date is more than 2 UINTs
            case 0x102:
                $thisTagData = '';
                fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                for ($tx=0; $tx<$EXIFBlock->getSize(); $tx++) {
                    $thisTagData .= exif_readUINT16($file, $endian) . " ";
                }
                $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$thisTagData, "Group" => $EXIFBlock->getGroup()];
                break;
            case 0xC640: // image segmentation
                $segment = [];
                $segmentation = '';
                fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                for ($tx=0; $tx<$EXIFBlock->getSize(); $tx++) {
                    array_push($segment, exif_readUINT16($file, $endian));
                }
                $segmentation = "Segments: " . $segment[0]+1;
                $segmentation .= "<br>Segment width: " . $segment[1] . "px  ";
                $segmentation .= "<br>Last Segment width: " . $segment[2] . "px  ";

                $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$segmentation, "Group" => $EXIFBlock->getGroup()];
                break;
                


        // others
        case 0x0002: // Interop version
        case 0x9000: // EXIF Version
       
            $exifVersion = $EXIFBlock->getPointer();
            if ($endian==true) {
                $byte[0] = $exifVersion >> 24;
                $byte[1] = ($exifVersion & 0xFFFFFF) >> 16;
                $byte[2] = ($exifVersion & 0xFFFF)  >> 8;
                $byte[3] = ($exifVersion & 0xFF);
            } else {
                $byte[3] = $exifVersion >> 24;
                $byte[2] = ($exifVersion & 0xFFFFFF) >> 16;
                $byte[1] = ($exifVersion & 0xFFFF)  >> 8;
                $byte[0] = ($exifVersion & 0xFF);
            }
            $exifVersionShow = '';
            for ($ix=0; $ix<4; $ix++) {
                $exifVersionShow .= chr($byte[$ix]);
            }
            $exifVersionShow = substr_replace(ltrim($exifVersionShow,"0"),".",1,0);
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$exifVersionShow, "Group" => $EXIFBlock->getGroup()];
            break;
            
        case 0x9101: // Components Configuration
            $compConfig = $EXIFBlock->getPointer();
            if ($endian==true) {
                $byte[0] = $compConfig >> 24;
                $byte[1] = ($compConfig & 0xFFFFFF) >> 16;
                $byte[2] = ($compConfig & 0xFFFF)  >> 8;
                $byte[3] = ($compConfig & 0xFF);
            } else {
                $byte[3] = $compConfig >> 24;
                $byte[2] = ($compConfig & 0xFFFFFF) >> 16;
                $byte[1] = ($compConfig & 0xFFFF)  >> 8;
                $byte[0] = ($compConfig & 0xFF);
            }
            $compConfigShow = '';
            for ($ix=0; $ix<4; $ix++) {
                $compConfigShow .= $ENUM_ComponentsConfig[$byte[$ix]];
            }
            $EXIFItem = ["Tag"=>$ENUM_EXIFTags[$EXIFBlock->getTag()],"Data"=>$compConfigShow, "Group" => $EXIFBlock->getGroup()];
            unset($compConfigShow);
            break;
            
      
                

        // do nothing/ignore these tags
        case 0x0111: // thumbnail pointer
        case 0x0115: // sam,ples per pixel
        case 0x0116: //rows per strip
        case 0x13C: // host computer
        case 0x0213:
        case 0x0201: // thumbnail pointer
        case 0x02BC: // XMP Pointer
        case 0x8769: // EXIF Pointer
        case 0x9102:
        case 0x9214: // Subject area
        case 0x9290: // sub sec of time
        case 0x9291: // sub sec of time
        case 0x9292: // sub sec of time
        case 0xA000: // Flashpix version
        case 0xA005: // interop pointer
        case 0xA460: // composite image
        case 0xA300:
        case 0xA40C:
        case 0xA301: // scene type
        case 0xC4A5:
        case 0xC5D8: // unknown
        case 0xC5D9: // unknown
        case 0xC6C5: // SRaw type
        case 0xC6DC: // unknown
            break;    



        // pointers to additional data structures
        case 0x8825: // GPS Info  
            if ($GPSPointer!=0) {
                fseek($file, $GPSPointer);
                $blocks = getTagsToArray($file, $endian,5);
                $latDirection = "";
                $longDirection = "";
                $latCoord = "";
                $longCoord = "";
                $altDirection = "";
                $altCoord = "";
                $altitude = "";
                foreach ($blocks as $EXIFBlock) {
                    $EXIFItem =[];
                    switch ($EXIFBlock->getTag()) {
                        case 0x0001: // Latitude Direction
                        case 0x0003:
                            $string = $EXIFBlock->getPointer();
                            $string = exif_readIString($string,$endian,1);
                            switch ($string) {
                                case "N":
                                    $string = "North";
                                    break;
                                case "S":
                                    $string = "South";
                                    break;
                                case "E":
                                    $string = "East";
                                    break;
                                case "W":
                                    $string = "West";
                                    break;
                            }
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                         
                            break;
                            case 0x000C:
                                $string = $EXIFBlock->getPointer();
                                $string = exif_readIString($string,$endian,1);
                                switch ($string) {
                                    case "N":
                                        $string = "Knots";
                                        break;
                                    case "K":
                                        $string = "Kilometres per hour (kmh)";
                                        break;
                                    case "M":
                                        $string = "Miles per hour (mph)";
                                        break;  
                                }
                                $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                             
                                break;
                            case 0x0019:
                                $string = $EXIFBlock->getPointer();
                                $string = exif_readIString($string,$endian,1);
                                switch ($string) {
                                    case "N":
                                        $string = "Nautical miles";
                                        break;
                                    case "K":
                                        $string = "Kilometres";
                                        break;
                                    case "M":
                                        $string = "Miles";
                                        break;  
                                }
                                $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                                
                                break;
                        case 0x000E:
                        case 0x0010:
                        case 0x0017:
                            $string = $EXIFBlock->getPointer();
                            $string = exif_readIString($string,$endian,1);
                            switch ($string) {
                                case "M":
                                    $string = "Magnetic North";
                                    break;
                                case "T":
                                    $string = "True North";
                                    break; 
                            }
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                            
                            break;  
                        case 0x0008:
                        case 0x0009:
                        case 0x000A:
                        case 0x0012:
                        case 0x0013:
                        case 0x0015:
                            $string = $EXIFBlock->getPointer();
                            $string = exif_readIString($string,$endian,1);
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                            break;
                        case 0x001D:
                            $datestamp = exif_readString($file,$EXIFBlock->getPointer() + $jpegOffset, $EXIFBlock->getSize());
                            $datestamp = substr($datestamp,8,2) ."/" . substr($datestamp,5,2) ."/" . substr($datestamp,0,4);
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$datestamp, "Group" => $EXIFBlock->getGroup()];
                            break;

                        case 0x0000:
                        $GPSVersion = $EXIFBlock->getPointer();
                        if ($endian==true) {
                            $byte[0] = $GPSVersion >> 24;
                            $byte[1] = ($GPSVersion & 0xFFFFFF) >> 16;
                            $byte[2] = ($GPSVersion & 0xFFFF)  >> 8;
                            $byte[3] = ($GPSVersion & 0xFF);
                        } else {
                            $byte[3] = $GPSVersion >> 24;
                            $byte[2] = ($GPSVersion & 0xFFFFFF) >> 16;
                            $byte[1] = ($GPSVersion & 0xFFFF)  >> 8;
                            $byte[0] = ($GPSVersion & 0xFF);
                        }
                        $GPSVersionShow = '';
                        for ($ix=0; $ix<4; $ix++) {
                            $GPSVersionShow .= $byte[$ix];
                        }
                        $GPSVersionShow = substr_replace(trim($GPSVersionShow,"0"),".",1,0);
                        $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$GPSVersionShow, "Group" => $EXIFBlock->getGroup()];
                        break;
                        
                        case 0x0002: // Latitude Coordinate Rationals
                        case 0x0004: // Longitude Coordinate Rationals
                            $coord = $EXIFBlock->getPointer() + $jpegOffset;
                            $coord = exif_readRational($file,$coord,3,$endian);
                            $location = number_format($coord[0] + $coord[1]/60 + $coord[2] / 3600,6) . " (" . $coord[0] . "° " . $coord[1] . "' " .number_format($coord[2],3) . chr(34) . ")";
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$location, "Group" => $EXIFBlock->getGroup()];       
                            break;
                        case 0x0005: // Altitude reference
                            $altitudeRef = exif_readUINT16($file, $endian);
                            if ($altitudeRef==0) {
                                $altitudeRef = "Above sea level";
                            } elseif ($altitudeRef==1) {
                                $altitudeRef = "Below sea level";
                                $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$altitudeRef, "Group" => $EXIFBlock->getGroup()];
                            } else {
                                break;
                            }
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$altitudeRef, "Group" => $EXIFBlock->getGroup()];
                            unset($altitudeRef);
                            break;
                        case 0x0006: // altitude
                            $altitude = exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,1,$endian);
                            if ($altitude!=0) {
                                $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>number_format($altitude[0],2) . "m", "Group" => $EXIFBlock->getGroup()];
                            }
                            unset($altitude);
                            break;
                        case 0x0007: // timestamp
                            $timestamp = exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,3, $endian);
                            $timestamp[0] = str_pad($timestamp[0],2,"0",STR_PAD_LEFT);
                            $timestamp[1] = str_pad($timestamp[1],2,"0",STR_PAD_LEFT);
                            $timestamp[2] = str_pad($timestamp[2],2,"0",STR_PAD_LEFT);
                            
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$timestamp[0].":".$timestamp[1].":".$timestamp[2] , "Group" => $EXIFBlock->getGroup()];
                            break;
                        case 0x00D: // speed
                        case 0x00F: // track
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>number_format(exif_readRational($file,$EXIFBlock->getPointer()+$jpegOffset,1,$endian)[0],2), "Group" => $EXIFBlock->getGroup()];
                            break;

                        case 0x011: // img direction
                        case 0x018: // destination bearing    
                            $bearing = number_format(exif_readRational($file,$EXIFBlock->getPointer()+$jpegOffset,1,$endian)[0],2);
                            $direction = "";
                            switch (true) {
                                case ($bearing <=22.5):
                                    $direction = "North";
                                    break;
                                case ($bearing <=67.5):
                                    $direction = "North East";
                                    break;
                                case ($bearing <=112.5):
                                    $direction = "East";
                                    break;
                                case ($bearing <=157.5):
                                    $direction = "South East";
                                    break;
                                case ($bearing <=202.5):
                                    $direction = "South";
                                    break;
                                case ($bearing <=247.5):
                                    $direction = "South West";
                                    break;
                                case ($bearing <=292.5):
                                    $direction = "West";
                                    break;
                                case ($bearing <=337.5):
                                    $direction = "North West";
                                    break;
                                case ($bearing <=360):
                                    $direction = "North";
                                    break;
                                }
                            
                            $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$bearing . "° (" . $direction .")", "Group" => $EXIFBlock->getGroup()];
                            break;

                            case 0x001F: // accuracy
                                $accuracy = exif_readRational($file, $EXIFBlock->getPointer() + $jpegOffset,1,$endian);
                                if ($accuracy!=0) {
                                    $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>"Accurate to within ".number_format($accuracy[0],2) . " metres.", "Group" => $EXIFBlock->getGroup()];
                                }
                                unset($accuracy);
                                break;

                            default: // all other tags ignored
                             $EXIFItem = ["Tag"=>$ENUM_GPSTags[$EXIFBlock->getTag()],"Data"=>$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup()];
                        }
                        if ($EXIFItem) {
                            array_push($EXIFData, $EXIFItem);
                        } 
                        $EXIFItem = null;
                }   
            }
            break;

        case 0x927C:
            if ($MakernotePointer!=0 && $jpegOffset==0) {
                fseek($file, $MakernotePointer);
                $blocks = getTagsToArray($file, $endian,6);
                foreach ($blocks as $EXIFBlock) {
                    $EXIFItem =[];
                    switch ($EXIFBlock->getTag()) {
                    // strings
                    case 0x0006:
                    case 0x0007:
                    case 0x0009:
                    case 0x0095:
                    case 0x0096:
                        $string = exif_readString($file,$EXIFBlock->getPointer() + $jpegOffset, $EXIFBlock->getSize());
                        if ($string) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$string, "Group" => $EXIFBlock->getGroup()];
                        }
                        break;
                    case 0x0008:
                    case 0x000C:
                    case 0x000E:
                    case 0x001E:
                    case 0x0028:
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup()];
                        break;
                    case 0x0010:
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$ENUM_MNModelID[$EXIFBlock->getPointer()], "Group" => $EXIFBlock->getGroup()];
                        break;
                    case 0x0013:
                        fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                        $thumbnailArea = [];
                        for ($tx=0; $tx<$EXIFBlock->getSize(); $tx++) {
                            array_push($thumbnailArea, exif_readUINT16($file, $endian));
                        }
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"x,y = (" .$thumbnailArea[0] ."," .$thumbnailArea[2] .") size = (" .$thumbnailArea[1] ."," .$thumbnailArea[3] .")", "Group" => $EXIFBlock->getGroup()];
                        break;
                    case 0x0015:
                        if ($EXIFBlock->getPointer()==0x90000000) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"Format 1", "Group" => $EXIFBlock->getGroup()];
                        } elseif ($EXIFBlock->getPointer()==0xA0000000) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"Format 2", "Group" => $EXIFBlock->getGroup()];
                        }
                        break;
                    case 0x001A:
                        if ($EXIFBlock->getPointer()>1) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"On", "Group" => $EXIFBlock->getGroup()];
                        }
                        break;
                    case 0x001C:
                        if ($EXIFBlock->getPointer()==1) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"Date only", "Group" => $EXIFBlock->getGroup()];
                        } elseif ($EXIFBlock->getPointer()==2) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"Date and Time", "Group" => $EXIFBlock->getGroup()];
                        }
                        break;
                        
                    case 0x0038: // Battery
                        $batterytype = exif_readString($file, $EXIFBlock->getPointer()+4 + $jpegOffset, $EXIFBlock->getSize()-4);
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$batterytype, "Group" => $EXIFBlock->getGroup()];
                        break;
                    case 0x0098:
                        fseek ($file, $EXIFBlock->getPointer() + $jpegOffset);
                        $cropTags = [];
                        for ($crix=0; $crix<$EXIFBlock->getSize(); $crix++) {
                            array_push($cropTags, exif_readUINT16($file, $endian));
                        };
                        if (($cropTags[0]+$cropTags[1]+$cropTags[2]+$cropTags[3])!=0) {
                            $EXIFItem = ["Tag"=>"Left Margin", "Data"=>$cropTags[0], "Group"=> 13];
                            array_push($EXIFData, $EXIFItem);
                            $EXIFItem = ["Tag"=>"Right Margin", "Data"=>$cropTags[1], "Group"=> 13];
                            array_push($EXIFData, $EXIFItem);
                            $EXIFItem = ["Tag"=>"Top Margin", "Data"=>$cropTags[2], "Group"=> 13];
                            array_push($EXIFData, $EXIFItem);
                            $EXIFItem = ["Tag"=>"Bottom Margin", "Data"=>$cropTags[3], "Group"=> 13];
                            array_push($EXIFData, $EXIFItem);
                            $EXIFItem = [];
                        }
                        break;

                    case 0x009A:
                        fseek ($file, $EXIFBlock->getPointer() + $jpegOffset);
                        $aspectRatio = exif_readUINT32($file, $endian);
                        $EXIFItem = ["Tag"=> $ENUM_MNAspectTags[0],"Data"=>$ENUM_MNAspect[$aspectRatio], "Group" => 19];
                        array_push($EXIFData, $EXIFItem);
                        $acrops = [];
                        for ($aix=0; $aix<4; $aix++) {
                            array_push($acrops, exif_readUINT32($file, $endian));
                        }
                        $EXIFItem = ["Tag"=> "Image Top Left","Data"=> "(" .$acrops[2] .", " . $acrops[3] .")", "Group" => 19];
                        array_push($EXIFData, $EXIFItem);        
                        $EXIFItem = ["Tag"=> "Image Size","Data"=> $acrops[0] ." x " . $acrops[1], "Group" => 19];
                        array_push($EXIFData, $EXIFItem);        
                       
                        unset ($acrops);
                        $EXIFItem = [];
                        break;
                    
                    case 0x00AE:
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$EXIFBlock->getPointer() ."K", "Group" => $EXIFBlock->getGroup()];
                                
                    case 0x00B4:
                        $colourSpace = $EXIFBlock->getPointer();
                        if ($colourSpace==1) {
                            $colourSpace ="sRGB";
                        } elseif ($colourSpace==2) {
                            $colourSpace = "Adobe RGB";
                        } else {
                            break;
                        }
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$colourSpace, "Group" => $EXIFBlock->getGroup()];
                        break;
                    
                    case 0x4008:
                    case 0x4009:
                  
                        $styles = exif_readUINT16($file, $endian,3,$EXIFBlock->getPointer() + $jpegOffset);
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$ENUM_PictureStyle[$styles[0]], "Group" => $EXIFBlock->getGroup()];
                        break;
                    case 0x4010:
                        $customStyle = exif_readString($file,$EXIFBlock->getPointer() + $jpegOffset, $EXIFBlock->getSize());
                        if ($customStyle) {
                            $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$customStyle, "Group" => $EXIFBlock->getGroup()];
                        }
                        break;
                    case 0x4021:
                        fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                        exif_readUINT32($file, $endian); // skip size
                        $multi = exif_readUINT32($file, $endian);
                        if ($multi==0) {
                            break;
                        }       
                        if ($multi==2) {
                            $multi = "On (RAW)";
                        } else {
                            $multi = "On";
                        }
                        $multiControl = exif_readUINT32($file, $endian);
                        if ($multiControl==3) {
                            $multiControl = "Dark (comparative)";
                        } elseif ($multiControl==2) {
                            $multiControl = "Bright (comparative)";
                        } elseif ($multiControl==1) {
                            $multiControl = "Average";
                        } else {
                            $multiControl = "Additive";
                        }
                        $multiShots = exif_readUINT32($file, $endian);
                        $EXIFItem = ["Tag"=>"Multiple exposure","Data"=>$multi, "Group" => 20];
                        array_push($EXIFData, $EXIFItem);
                        $EXIFItem = ["Tag"=>"Control","Data"=>$multiControl, "Group" => 20];
                        array_push($EXIFData, $EXIFItem);
                        $EXIFItem = ["Tag"=>"Number of shots","Data"=>$multiShots, "Group" => 20];
                        break;                         


                    case 0x4025:
                        fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                        $HDR = exif_readSINT32($file,$endian);
                        if ($HDR==2) {
                            $HDR = "On";
                        } elseif ($HDR==1) {
                            $HDR = "Auto";
                        } else {
                            $HDR = "Off";
                        }

                        if ($HDR!="Off") {
                            $HDREffect = exif_readSINT32($file, $endian);
                            switch ($HDREffect) {
                                case 0: $HDREffect = "Natural"; break;
                                case 1: $HDREffect = "Art (standard)"; break;
                                case 2: $HDREffect = "Art (vivid)"; break;
                                case 3: $HDREffect = "Art (bold)"; break;
                                case 4: $HDREffect = "Art (embossed)"; break;
                                default: $HDREffect = "None";
                            }
                            $EXIFItem = ["Tag"=>"High Dynamic Range","Data"=>$HDR . " with effect " . $HDREffect, "Group" => $EXIFBlock->getGroup()];
                            break;
                    
                        }
                        break;
                        

                    case 0x0001:
                        $settingsPointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $settingsPointer);
                        $csSize = exif_readUINT16($file,$endian)/2;
                        $csblocks = [];
                        for ($csx=0; $csx<$csSize; $csx++) {
                            array_push($csblocks, exif_readSINT16($file, $endian));
                        }
                        for ($csx=0; $csx<$csSize; $csx++) {
                            $EXIFItem = [];
                            switch ($csx) {
                                case 0:
                                    $macromode = $csblocks[$csx];
                                    if ($macromode==1) {
                                        $macromode = "Macro";
                                    } elseif ($macromode==2) {
                                        $macromode = "Normal";
                                    } else {
                                        $macromode = "not recorded";
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$EXIFBlock->getTag()],"Data"=>$macromode, "Group" => 7];
                                    unset ($macromode);
                                    break;
                                case 1:
                                    if ($csblocks[$csx]!=0) {
                                        $selftimer = $csblocks[$csx] . " secs";
                                    } else {
                                        $selftimer = 'Not used';
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$selftimer, "Group" => 7];
                                    unset ($selftimer);
                                    break;
                                case 2:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSQuality[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 3:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSFlash[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 4:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSDrive[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 6:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSFocusMode[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 8:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSRecordMode[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 9:
                                $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSImageSize[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 10:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSEasyMode[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 11:
                                    if ($csblocks[$csx]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>pow(2,$csblocks[$csx]) ."x zoom", "Group" => 7];
                                    }
                                    break;    
                                case 12:
                                case 13:
                                    if ($csblocks[$csx]>16384) {$csblocks[$csx] = $csblocks[$csx] - 32768;}            
                                    if ($csblocks[$csx]==0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>"Normal", "Group" => 7];
                                    } else {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$csblocks[$csx], "Group" => 7];
                                    }
                                    break;
                                case 16:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSMeterMode[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 17:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSFocusRange[$csblocks[$csx]], "Group" => 7];
                                    break;
                                case 18:
                                    if ($csblocks[$csx]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSAFPoint[$csblocks[$csx]], "Group" => 7];            
                                    }              
                                    break;
                                case 19:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSExposureMode[$csblocks[$csx]], "Group" => 7];            
                                    break;
                                case 21:
                                    if ($csblocks[$csx]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSLensType[$csblocks[$csx]], "Group" => 7];            
                                    }
                                    break;
                                case 22:
                                case 23:
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$csblocks[$csx] . "mm", "Group" => 7];            
                                    break;
                                case 25:
                                case 26:
                                    $aperture = "ƒ/" . number_format(pow(2,$csblocks[$csx]/64),0);   
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$aperture, "Group" => 7];            
                                    unset($aperture);
                                    break;
                                case 27:
                                    if ($csblocks[$csx]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$csblocks[$csx], "Group" => 7];
                                    }
                                    break;
                                case 28:
                                    $flashbits = "";
                                    if ($csblocks[$csx]!=0) {
                                       if ($csblocks[$csx] & 1) {
                                            $flashbits .="Manual, ";
                                        }
                                        if ($csblocks[$csx] & 2) {
                                            $flashbits .="TTL:, ";
                                        }
                                        if ($csblocks[$csx] & 4) {
                                            $flashbits .="A-TTL, ";
                                        }
                                        if ($csblocks[$csx] & 8) {
                                            $flashbits .="E-TTL, ";
                                        }
                                        if ($csblocks[$csx] & 16) {
                                            $flashbits .="FP Sync Enabled, ";
                                        }
                                        if ($csblocks[$csx] & 128) {
                                            $flashbits .="Second curtain sync, ";
                                        }
                                        if ($csblocks[$csx] & 2048) {
                                            $flashbits .="FP Sync Enabled, ";
                                        }
                                        if ($csblocks[$csx] & 8192) {
                                            $flashbits .="Built in, ";
                                        }
                                        if ($csblocks[$csx] & 16384) {
                                            $flashbits .="External, ";
                                        }
                                        if ($flashbits) {
                                            $flashbits = substr($flashbits,0,-2);
                                            $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$flashbits, "Group" => 7];    
                                        }
                                    }
                                    unset($flashbits);
                                    break;
                                case 31:
                                    if ($csblocks[$csx]==8) {
                                        $focusType = "Manual";
                                    } elseif ($csblocks[$csx]==1) {
                                        $focusType = "Continuous";
                                    } else {
                                        $focusType= "Single";
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$focusType, "Group" => 7];    
                                    unset($focusType);
                                    break;    
                                case 32:
                                    if ($csblocks[$csx]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSAESetting[$csblocks[$csx]], "Group" => 7];            
                                    }
                                    break;
                                case 33:
                                    if ($csblocks[$csx]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSStabilisation[$csblocks[$csx]], "Group" => 7];            
                                    }
                                    break;
                                case 38:
                                    if ($csblocks[$csx]==0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>"Centre", "Group" => 7];                
                                    } elseif ($csblocks[$csx]==1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>"AF Point", "Group" => 7];                
                                    }
                                    break;
                                case 39:
                                    if ($csblocks[$csx]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSPhotoEffect[$csblocks[$csx]], "Group" => 7];            
                                    }
                                    break;
                                case 40:
                                    if ($csblocks[$csx]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNCameraSettings[$csx+1],"Data"=>$ENUM_MNCSFlashOutput[$csblocks[$csx]], "Group" => 7];            
                                    }
                                    break;
                                                                    
                                // unknown tags or not shown/required
                                case 5:
                                case 7:
                                case 14: // sharpness
                                case 15: // ISO
                                case 20:
                                case 24: // focal units
                                case 29:
                                case 30:
                                case 34: // display aperture
                                case 35: // zoom source width
                                case 36: // zoom traget width
                                case 37:
                                case 41: // Colour tone
                                case 42:
                                case 43:
                                case 44:
                                case 45: // sRAW quality
                                case 46:
                                case 47:
                                case 48:
                                case 49:
                            
                                    break;
                        
                                default:
                                    break;
                            
                             
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                            }
                        }
                        unset($csblocks);
                        unset ($settingsPointer);
                       break;
                    
                    case 0x0004:
                        $infoPointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $infoPointer);
                        $siSize = exif_readUINT16($file,$endian)/2;
                        $siblocks = [];
                        for ($six=0; $six<$siSize; $six++) {
                            array_push($siblocks, exif_readSINT16($file, $endian));
                        }
                        for ($six=0; $six<$siSize; $six++) {
                            $EXIFItem = [];
                            switch ($six) {
                                case 0x2: // Measured EV
                                $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]/32)+5, "Group" => 8];  
                                break;                                            
                                case 0x3:
                                case 0x14:
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>"ƒ/" . number_format(pow(2,($siblocks[$six]/64)),1), "Group" => 8];  
                                    break;
                                case 0x4:
                                case 0x15:
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>ToFraction(1/pow(2,($siblocks[$six]/32))) . " sec", "Group" => 8];  
                                    break;
                                case 0x5:
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>ToFraction($siblocks[$six]), "Group" => 8];  
                                    break;
                                case 0x6:
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$ENUM_MNSIWhiteBalance[$siblocks[$six]], "Group" => 8];           
                                break; 
                                case 0x7:
                                    $slowShutter = '';
                                    if ($siblocks[$six]==1) {
                                        $slowShutter = "Night scene";
                                    } elseif ($siblocks[$six]==2) {
                                        $slowShutter = "On";
                                    } else {
                                        unset($slowShutter);
                                        break;
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$slowShutter, "Group" => 8];  
                                    unset($slowShutter);
                                    break;

                                case 0xB:
                                    if ($siblocks[$six]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]-128) . "°C", "Group" => 8];  
                                    }
                                    break;
                                case 0xC:
                                case 0xE:
                                case 0x10:
                                    if ($siblocks[$six]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]), "Group" => 8];         
                                    }
                                    break;
                                case 0xD:
                                    if ($siblocks[$six]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$ENUM_MNSIAFinFocus[$siblocks[$six]], "Group" => 8];           
                                    }
                                    break;
                                case 0xF:
                                    $AEB = '';
                                    if ($siblocks[$six]==-1) {
                                        $AEB = "On";
                                    } elseif ($siblocks[$six]>0) {
                                        $AEB = "On - shot " . $siblocks[$six];
                                    } else {
                                        unset($AEB);
                                        break;
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$AEB, "Group" => 8];         
                                    unset($AEB);
                                    break;
                                case 0x11:
                                    $controlMode = $siblocks[$six];
                                    if ($controlMode==1) {
                                        $controlMode = "Camera local control";
                                    } elseif ($controlMode==3) {
                                        $controlMode = "Computer remote control";
                                    } else {
                                        break;
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$controlMode, "Group" => 8];         
                                    unset($controlMode);
                                    break;
                                
                                case 0x12:
                                case 0x13:
                                    if ($siblocks[$six]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]/100) . "m", "Group" => 8];                                              
                                    }
                                    break;
                                case 0x16: // Measured EV
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]/8)-6, "Group" => 8];  
                                    break; 
                                case 0x17:
                                    if ($siblocks[$six]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>($siblocks[$six]/10) . " secs", "Group" => 8];  
                                    }
                                    break;
                                case 0x19:
                                    if ($siblocks[$six]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$ENUM_MNSICameraType[$siblocks[$six]], "Group" => 8];           
                                    }
                                    break;
                                case 0x1A:
                                    if ($siblocks[$six]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$ENUM_MNSIRotation[$siblocks[$six]], "Group" => 8];           
                                    }
                                    break;
                                case 0x1B:
                                    if ($siblocks[$six]==1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>"On", "Group" => 8];           
                                    }
                                    break;
                                case 0x1C: // self timer
                                    if ($siblocks[$six]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$siblocks[$six] . " secs", "Group" => 8];                           
                                    }
                                break;
                                
                                
                                    // hidden/unused tags
                                case 0x0: // Auto ISO 
                                case 0x1: // Base ISO
                                case 0x8: // sequence number
                                case 0x9: // optical zoom code
                                case 0xA: // unknown
                                case 0x18: // unknown
                                case 0x1D: // unknown
                                case 0x1E: // unknown
                                case 0x1F: // unknown
                                case 0x20: // Flash output
                                case 0x21: // unknown
                                case 0x22: // unknown
                                case 0x23: // unknown
                                    break;

                                default:
                                    $EXIFItem = ["Tag"=>$ENUM_MNShotInfo[$six+1],"Data"=>$siblocks[$six], "Group" => 8];
                                    break;
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                            }
                        }
                    break;
  
                    case 0x0035:
                        $timerPointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $timerPointer);
                        $tSize = exif_readUINT32($file,$endian)/4 - 1; // -1 to skip size
                        $tblocks = [];
                        for ($tix=0; $tix<$tSize; $tix++) {
                            array_push($tblocks, exif_readSINT32($file, $endian));
                        }
                        for ($tix=0; $tix<$tSize; $tix++) {
                            $EXIFItem = [];
                            switch ($tix) {
                                case 0x0000:
                                    $UTC = "UTC ";
                                    if ($tblocks[$tix]>0) {
                                        $thours = intval($tblocks[$tix]/60);
                                        $tmins = $tblocks[$tix] % 60;
                                        $UTC .= "+ " . sprintf('%02d', abs($thours)) .":" . sprintf('%02d', abs($tmins));
                                    } elseif ($tblocks[$tix]<0) {
                                        $thours = intval($tblocks[$tix]/60);
                                        $tmins = $tblocks[$tix] % 60;
                                        $UTC .= "- " . sprintf('%02d', abs($thours)) .":" . sprintf('%02d', abs($tmins));
                                    } 
                                    $EXIFItem = ["Tag"=>$ENUM_TimeInfo[$tix+1],"Data"=>$UTC, "Group" => 10];
                                    unset($thours);
                                    unset ($tmins);
                                    unset ($UTC);
                                    break;
                                case 0x0001:
                                    $EXIFItem = ["Tag"=>$ENUM_TimeInfo[$tix+1],"Data"=>$ENUM_TITimeZone[$tblocks[$tix]], "Group" => 10];
                                    break;
                                 case 0x0002:
                                    if ($tblocks[$tix]==60) {
                                        $EXIFItem = ["Tag"=>$ENUM_TimeInfo[$tix+1],"Data"=>"On", "Group" => 10];
                                    } else {
                                        $EXIFItem = ["Tag"=>$ENUM_TimeInfo[$tix+1],"Data"=>"Off", "Group" => 10];     
                                    }
                                    break;
                                default:
                                    break;
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                                $EXIFItem = [];
                            }
                        }
                    break;

                    case 0x0093:
                        $filePointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $filePointer);
                        $fileSize = exif_readUINT16($file,$endian)/2;
                        $fileblocks = [];
                        for ($filex=0; $filex<$fileSize; $filex++) {
                            array_push($fileblocks, exif_readSINT16($file, $endian));
                        }
                        for ($filex=0; $filex<$fileSize; $filex++) {
                            $EXIFItem = [];;
                            $bracketMode ='Off';
                            $wbbracketmode = 'Off';
                            switch ($filex) {
                                case 0x02: // bracket mode
                                    $bracketMode = $ENUM_MNBracketMode[$fileblocks[$filex]];
                                    $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$bracketMode, "Group" => 14];
                                    break;
                                case 0x03:
                                case 0x04:
                                    if ($bracketMode!="Off") {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$fileblocks[$filex], "Group" => 14];
                                    }
                                    break;
                                case 0x05:
                                    if ($fileblocks[$filex]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$ENUM_MNRAWJPGQuality[$fileblocks[$filex]], "Group" => 14];
                                    }
                                    break;
                                case 0x06:
                                    if ($fileblocks[$filex]!=-1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$ENUM_MNRAWJPGSize[$fileblocks[$filex]], "Group" => 14];
                                    }
                                    break;    
                                case 0x07:
                                        $longExposureNR=$fileblocks[$filex];
                                    if ($longExposureNR==4) {
                                        $longExposureNR = "Automatic"; 
                                    } elseif ($longExposureNR==3 || $longExposureNR==1) {
                                        $longExposureNR = "On";
                                    } else {
                                        break;
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$longExposureNR, "Group" => 14];
                                    break;
                                case 0x08:
                                    if ($fileblocks[$filex]==2) {
                                        $wbbracketmode = "On (shift GM)";
                                    } elseif ($fileblocks[$filex]==1) {
                                        $wbbracketmode = "On (shift AB)";
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$wbbracketmode, "Group" => 14];
                                    break;
                                
                                case 0x0B:
                                case 0x0C:
                                    if ($wbbracketmode!="Off") {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$fileblocks[$filex], "Group" => 14];          
                                    }
                                    break;
                                case 0x0D:
                                    if ($fileblocks[$filex]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$ENUM_MNFilterEffect[$fileblocks[$filex]], "Group" => 14];
                                    }
                                    break; 
                                case 0x0E:
                                    if ($fileblocks[$filex]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$ENUM_MNToningEffect[$fileblocks[$filex]], "Group" => 14];
                                    }
                                    break;
                                case 0x12:
                                case 0x18:
                                    if ($fileblocks[$filex]==1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>"On", "Group" => 14];     
                                    } else {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>"Off", "Group" => 14];  
                                    }
                                    break;
                                case 0x13:
                                case 0x14:
                                    $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>$fileblocks[$filex]/100 ."m", "Group" => 14];
                                    break;
                                case 0x16:
                                    if ($fileblocks[$filex]==2) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>"Electronic", "Group" => 14];     
                                    } elseif ($fileblocks[$filex]==1) {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>"Electronic First Curtain", "Group" => 14];  
                                    } else {
                                        $EXIFItem = ["Tag"=>$ENUM_MNFileInfo[$filex+1],"Data"=>"Mechanical", "Group" => 14];                  
                                    }
                                    break;
                    
                                default:
                                    break;
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                            }
                        }
                    break;

                    case 0x0099: // Custom functions
                        fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                        exif_readUINT32($file, $endian); // total size
                        $numberOfBlocks = exif_readUINT32($file, $endian);
                        $functionItems = [];
                        for ($cf=0; $cf<$numberOfBlocks; $cf++) {
                            $blockNumber = exif_readUINT32($file, $endian);
                            $blockSize = exif_readUINT32($file, $endian);
                            $blockCount = exif_readUINT32($file, $endian);
                            for ($cfblock=0; $cfblock<$blockCount; $cfblock++) {
                                $thisTag = exif_readUINT32($file, $endian);
                                $thisSize = exif_readUINT32($file, $endian);
                                $theseValues =[];
                                for ($gv=0; $gv<$thisSize; $gv++) {
                                    array_push($theseValues, exif_readSINT32($file, $endian));
                                }
                                array_push($functionItems,["Group"=>$blockNumber, "Tag"=>$thisTag, "ValueCount"=>$thisSize, "Values"=>$theseValues]);
                            }
                        }

                        foreach ($functionItems as $key=>$functionItem) {
                            $EXIFItem =[];
                            switch ($functionItem["Tag"]) {
                                case 0x0101:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "1/2 stop";
                                    } else {
                                        $dataItem = "1/3 stop";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;

                                case 0x0102:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "1 stop";
                                    } else {
                                        $dataItem = "1/3 stop";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                           
                                case 0x0103:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "On";
                                    } else {
                                        $dataItem = "Off";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                                        
                                case 0x0104:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "Off";
                                    } else {
                                        $dataItem = "On";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                
                                case 0x0105:
                                    if ($functionItem["Values"][0]==2) {
                                        $dataItem = "+, 0, -";
                                    } elseif ($functionItem["Values"][0]==1) {
                                        $dataItem = "-, 0, +";
                                    } else {
                                        $dataItem = "0, -, +";     
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                case 0x0106:
                                    if ($functionItem["ValueCount"]==1) {
                                        switch ($functionItem["Values"][0]) {
                                            case 0: {$dataItem="3 shots"; break;}
                                            case 1: {$dataItem="2 shots"; break;}
                                            case 2: {$dataItem="5 shots"; break;}
                                            case 3: {$dataItem="7 shots"; break;}
                                        }
                                    } elseif ($functionItem["ValueCount"]==2) {
                                        $shotcount = $functionItem["Values"][0] . $functionItem["Values"][1];
                                        switch ($shotcount) {
                                            case "21": {$dataItem="2 shots"; break;}
                                            case "30": {$dataItem="3 shots"; break;}
                                            case "52": {$dataItem="5 shots"; break;}
                                            case "73": {$dataItem="7 shots"; break;}
                                        }
                                      }  
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                case 0x0107:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "Enabled (use active AF Point)";
                                    } else {
                                        $dataItem = "Disabled (use centre AF Point)";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                case 0x0108:
                                    if ($functionItem["Values"][0]==2) {
                                        $dataItem = "Enabled - ISO Speed";
                                    } elseif ($functionItem["Values"][0]==1) {
                                        $dataItem = "Enabled - Tv/Av";
                                    }  {
                                        $dataItem = "Disabled";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                case 0x010B:
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$ENUM_CFMeteringMode[$functionItem["Values"][0]], "Group"=>$functionItem["Group"]+14];
                                    break;      
                                case 0x0109:
                                case 0x010A:
                                case 0x010C:
                                case 0x010D:
                                case 0x010E:
                                    if ($functionItem["Values"][0]==1) {
                                        $dataItem = "Enabled";
                                    } else {
                                        $dataItem = "Disabled";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;    
                                case 0x0201:
                                    if ($functionItem["Values"][0]==2) {
                                        $dataItem = "On";
                                    } elseif ($functionItem["Values"][0]==1) {
                                        $dataItem = "Automatic";
                                    } else {
                                        $dataItem = "Off";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;   
                                case 0x0202:
                                case 0x0204:
                                    if ($functionItem["Values"][0]==3) {
                                        $dataItem = "Off";
                                    } elseif ($functionItem["Values"][0]==2) {
                                        $dataItem = "Strong";
                                    } elseif ($functionItem["Values"][0]==1) {
                                        $dataItem = "Low";
                                    } else {
                                        $dataItem = "Standard";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;
                                case 0x060F:
                                    if ($functionItem["Values"][0]==2) {
                                        $dataItem = "Enabled - down with set";
                                    } elseif ($functionItem["Values"][0]==1) {
                                        $dataItem = "Enabled";
                                    } else {
                                        $dataItem = "Disabled";
                                    }
                                    $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$dataItem, "Group"=>$functionItem["Group"]+14];
                                    break;   

                                    
                                default:
                                //  $EXIFItem = ["Tag"=> $ENUM_MCCustomFuncs[$functionItem["Tag"]], "Data"=>$functionItem["Values"], "Group"=>$functionItem["Group"]+14];
                            }



                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                                $EXIFItem = null;
                            }

                        }
                        
                        break;

                    case 0x00A0: // Processing info
                        $processingPointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $processingPointer);
                        $procSize = exif_readUINT16($file,$endian)/2 - 1; // -1 to skip size
                        $procblocks = [];
                        for ($procix=0; $procix<$procSize; $procix++) {
                            array_push($procblocks, exif_readSINT16($file, $endian));
                        }
                        for ($procix=0; $procix<$procSize; $procix++) {
                            $EXIFItem = [];
                            switch ($procix) {
                                case 0x0000:
                                    $tonecurve = $procblocks[$procix];
                                    if ($tonecurve==2) {
                                        $tonecurve = "Custom";
                                    } elseif ($tonecurve==1) {
                                        $tonecurve = "Manual";
                                    } else {
                                        $tonecurve = "Standard";
                                    }
                                    $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$tonecurve, "Group" => 12];
                                    unset($tonecurve);
                                    break;
                                case 0x0001:
                                case 0x0003:
                                case 0x0004:
                                case 0x0005:
                                case 0x0006:
                                case 0x000A:
                                    if ($procblocks[$procix]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix], "Group" => 12];
                                    }
                                    break;
                                case 0x0008:
                                    $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix] ."K", "Group" => 12];
                                    break;
                                case 0x0002:
                                    if ($procblocks[$procix]!=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$EXIF_MNSharpnessFreq[$procblocks[$procix]], "Group" => 12];
                                    }
                                    break;
                                case 0x0007:
                                    if ($procblocks[$procix]>=0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$ENUM_MNSIWhiteBalance[$procblocks[$procix]], "Group" => 12];
                                    }
                                    break;
                                case 0x0009:
                                    if ($procblocks[$procix]< 0xFF) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$ENUM_PictureStyle[$procblocks[$procix]], "Group" => 12];
                                    }
                                    break;
                                case 0x000B:
                                    if ($procblocks[$procix]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix] . "towards Amber", "Group" => 12];
                                    } elseif ($procblocks[$procix]<0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix] . "towards Blue", "Group" => 12];
                                    }
                                    break;
                                case 0x000C:
                                    if ($procblocks[$procix]>0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix] . "towards Green", "Group" => 12];
                                    } elseif ($procblocks[$procix]<0) {
                                        $EXIFItem = ["Tag"=>$ENUM_ProcessingInfo[$procix+1],"Data"=>$procblocks[$procix] . "towards Magenta", "Group" => 12];
                                    }
                                    break;
                                        
                                default:
                                    break;
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                                $EXIFItem = [];
                            }
                        }
                    break;

                    case 0x00E0: // sensor info
                        $sensorPointer = $EXIFBlock->getPointer() + $jpegOffset;
                        fseek($file, $sensorPointer);
                        $sensorSize = exif_readUINT16($file, $endian)/2 -1; // -1 to skip size
                        $sensorblocks = [];
                        for ($sensorx=0; $sensorx<$sensorSize; $sensorx++) {
                            array_push($sensorblocks, exif_readUINT16($file, $endian));
                        }
                        for ($sensorx=0; $sensorx<$sensorSize; $sensorx++) {
                            $EXIFItem = [];
                            switch ($sensorx) {
                                case 0x2:
                                case 0x3:
                                case 0xC:
                                case 0xD:
                                case 0xE:
                                case 0xF:
                                    break; 
                                default:
                                if ($sensorblocks[$sensorx]!=0) {
                                    $EXIFItem = ["Tag"=>$ENUM_MNSensorInfo[$sensorx+1],"Data"=>number_format($sensorblocks[$sensorx]), "Group" => 11];         
                                }
                            }
                            if ($EXIFItem) {
                                array_push($EXIFData, $EXIFItem);
                            }          
                        }
                        break;

                    case 0x00AA:  // Measured Colour
                        fseek($file, $EXIFBlock->getPointer() + $jpegOffset);
                        $mcSize = exif_readUINT16($file, $endian)/2 -1;
                        $mcblocks = [];
                        for ($mcx=0; $mcx<$mcSize; $mcx++) {
                            array_push($mcblocks, exif_readUINT16($file, $endian));
                        }
                        $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>"[Red,Green] = [" . $mcblocks[0] ."," . $mcblocks[1] . "] [Green,Blue] = [" .$mcblocks[2] . "," . $mcblocks[3] . "]", "Group" => $EXIFBlock->getGroup()];
                        break;
                      
                    // do nothing for these tags for now, possible future usage
                   
                    case 0x0002: // Focal length tags
                    case 0x0003: // Flash info
                    case 0x0005: // Panorama tags
                    case 0x000A: // unknown
                    case 0x000B: // unknown
                    case 0x000D: // Specific camera info tags
                    case 0x000F: // specific custom function tags
                    case 0x0011: // Movie tags
                    case 0x0012: // AF Info, this is in EXIF although may be required for future update if not
                    case 0x0014: // unknown                     
                    case 0x0016: // unknown
                    case 0x0017: // unknown
                    case 0x0018: // unknown
                    case 0x0019: // unknown
                    case 0x001B: // unknown
                    case 0x001D: // my colours
                    case 0x0023: // Categories
                    case 0x0024: // Face detection
                    case 0x0025: // Face detection
                    case 0x0026: // AF Info, this is in EXIF although may be required for future update if not
                    case 0x0027: // Contrast info
                    case 0x0083: // Original decision data
                    case 0x0097: // Dust delete data
                    case 0x00D0: // Vibration resolution
                    case 0x4001: // Colour data
                    case 0x4002: // CRW Parameters
                    case 0x4005: // Flavour
                    case 0x4011: // unknown
                    case 0x4012: // unknown
                    case 0x4013: // AF Micro adjustment
                    case 0x4015: // Vignetting correction
                    case 0x4016: // Vignetting correction
                    case 0x4018: // Lighting options
                    case 0x4019: // internal lens serial
                    case 0x4024: // filter
                    case 0x4020: // Ambience info
                    case 0x4027: // unknown


                        break;

                    default:   
                        // $EXIFItem = ["Tag"=>$ENUM_MakerTags[$EXIFBlock->getTag()],"Data"=>$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup()];
                        $EXIFItem = ["Tag"=>"0x" . strtoupper(dechex($EXIFBlock->getTag())),"Data"=>$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup()];
            
                    }  
                    
                    if ($EXIFItem) {
                        array_push($EXIFData, $EXIFItem);
                        $EXIFItem = null;
                    }
                }
            }
            break;
        
        
        
        
        default:
            $EXIFItem = ["Tag"=>"0x" . strtoupper(dechex($EXIFBlock->getTag())),"Data"=>$EXIFBlock->getPointer(), "Group" => $EXIFBlock->getGroup()];
        }

        if ($EXIFItem) {
            array_push($EXIFData, $EXIFItem);
        }
 
}

        fclose($file);   
        
        echo json_encode($EXIFData);

       
   

