<?php
//session_start();
// ******************
// * EXIFItem Class *
// ******************

class EXIFItem {
    //Properties
    private $Tag;
    private $Type;
    private $Size;
    public $Pointer;
    private $Group;
    
    
    //Methods
    function __construct($Tag, $Type, $Size, $Pointer, $Group) {
        $this ->Tag = $Tag;
        $this ->Type = $Type;
        $this ->Size = $Size;
        $this ->Pointer = $Pointer;
        $this ->Group = $Group;
    }
       
    public function setTag($Tag) {
        $this->Tag = $Tag;
    }
    public function getTag() {
        return $this->Tag;
    }
    public function setType($Type) {
        $this->Type = $Type;
    }
    public function getType() {
        return $this->Type;
    }
    public function setSize($Size) {
        $this->Size = $Size;
    }
    public function getSize() {
        return $this->Size;
    }
    public function setPointer($Pointer) {
        $this->Pointer = $Pointer;
    }
    public function getPointer() {
        return $this->Pointer;
    }
     public function setGroup($Group) {
        $this->Group = $Group;
    }
    public function getGroup() {
        return $this->Group;
    }
}  


class AFPoint {
    private $X;
    private $Y;
    private $Width;
    private $Height;
    
     function __construct($X, $Y, $Width, $Height) {
        $this ->X = $X;
        $this ->Y = $Y;
        $this ->Width = $Width;
        $this ->Height = $Height;
    }
    
    public function setX($X) {
        $this->X = $X;
    }
    public function getX() {
        return $this->X;
    }
    public function setY($Y) {
        $this->Y = $Y;
    }
    public function getY() {
        return $this->Y;
    }
    public function setWidth($Width) {
        $this->Width = $Width;
    }
    public function getWidth() {
        return $this->Width;
    }
    public function setHeight($Height) {
        $this->Height = $Height;
    }
    public function getHeight() {
        return $this->Height;
    }
    
}

?>