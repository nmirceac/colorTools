<?php

class Palette
{
    private $image = NULL;

    private $imageObject = NULL;



    private $precision = 5;

    private $colors = array();


    public function __construct($image)
    {
        if(!file_exists($image)) {
            throw new \Exception('Invalid filename');
        }

        if(filesize($image)<=11) {
            throw new \Exception('This is too small to be an image');
        }

        $this->image = $image;
        $this->getImageDetails();
    }

    private function getImageDetails()
    {
        $size = getimagesize($this->image);
        if(empty($size)) {
            throw new \Exception('This is not an image');
        }

        $this->type = substr($size['mime'], 6);
        $this->mime = $size['mime'];
        $this->width = $size[0];
        $this->height = $size[1];
    }

    private function createImageObject()
    {
        $this->imageObject = call_user_func('imagecreatefrom'.$this->type, $this->image);
    }

    public function getPalette()
    {
        $this->createImageObject();

        for($x=0; $x<$this->width; $x+=$this->precision) {
            for($y=0; $y<$this->height; $y+=$this->precision) {
                $color=Color::create($this->imageObject, $x, $y);
                print_r($color->getValue());
                echo PHP_EOL;

                $color = imagecolorat($this->imageObject, $x, $y);
                $color5=Color::create($color);
                print_r($color5->getValue());
                echo PHP_EOL;

                $rgb = imagecolorsforindex($this->imageObject, $color);

                $color2=Color::create($rgb);
                print_r($color2->getValue());
                echo PHP_EOL;
                $this_Rgb   = sprintf('#%02X%02X%02X', $rgb['red'], $rgb['green'], $rgb['blue']);

                $color3=Color::create($this_Rgb);
                print_r($color3->getValue());
                echo PHP_EOL;
                print_r($color3->getHex());
                echo PHP_EOL;
                print_r($color3->getRgb());
                echo PHP_EOL;
                echo $color3;
                echo PHP_EOL;
                print_r($color3->hex);
                echo PHP_EOL;
                print_r($color3->rgb);
                echo PHP_EOL;
                print_r($color3->int);
                echo PHP_EOL;
                print_r($color3->asd);
                echo PHP_EOL;

                $color4=Color::create(0x834B0E);
                print_r($color4->getValue());
                echo PHP_EOL;

                $color6=Color::create($color4);
                print_r($color6->getValue());
                echo PHP_EOL;

                echo PHP_EOL.'||';
                $color7=Color::create(0xabcdef);
                echo $color7->negate()->hex;
                echo PHP_EOL;


                exit();
            }
        }
    }
}

require_once 'Color.php';


$image = new Palette('test.jpg');
$image->getPalette();