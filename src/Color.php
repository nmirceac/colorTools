<?php

require_once('Color.php');
require_once('Image.php');
require_once('Palette.php');

class Color
{
    private $colors = array();
    private $value = null;
    private $name = null;

    const COMPARE_FAST   = 1;
    const COMPARE_NORMAL = 2;
    const COMPARE_GREAT  = 3;

    /*
     * The list of css colors
     * https://drafts.csswg.org/css-color/
     */
    public $cssColors = ['aliceblue'=>0xf0f8ff,'antiquewhite'=>0xfaebd7,'aqua'=>0x00ffff,'aquamarine'=>0x7fffd4,
        'azure'=>0xf0ffff,'beige'=>0xf5f5dc,'bisque'=>0xffe4c4,'black'=>0x000000,'blanchedalmond'=>0xffebcd,
        'blue'=>0x0000ff,'blueviolet'=>0x8a2be2,'brown'=>0xa52a2a,'burlywood'=>0xdeb887,'cadetblue'=>0x5f9ea0,
        'chartreuse'=>0x7fff00,'chocolate'=>0xd2691e,'coral'=>0xff7f50,'cornflowerblue'=>0x6495ed,'cornsilk'=>0xfff8dc,
        'crimson'=>0xdc143c,'cyan'=>0x00ffff,'darkblue'=>0x00008b,'darkcyan'=>0x008b8b,'darkgoldenrod'=>0xb8860b,
        'darkgray'=>0xa9a9a9,'darkgreen'=>0x006400,'darkgrey'=>0xa9a9a9,'darkkhaki'=>0xbdb76b,'darkmagenta'=>0x8b008b,
        'darkolivegreen'=>0x556b2f,'darkorange'=>0xff8c00,'darkorchid'=>0x9932cc,'darkred'=>0x8b0000,
        'darksalmon'=>0xe9967a,'darkseagreen'=>0x8fbc8f,'darkslateblue'=>0x483d8b,'darkslategray'=>0x2f4f4f,
        'darkslategrey'=>0x2f4f4f,'darkturquoise'=>0x00ced1,'darkviolet'=>0x9400d3,'deeppink'=>0xff1493,
        'deepskyblue'=>0x00bfff,'dimgray'=>0x696969,'dimgrey'=>0x696969,'dodgerblue'=>0x1e90ff,'firebrick'=>0xb22222,
        'floralwhite'=>0xfffaf0,'forestgreen'=>0x228b22,'fuchsia'=>0xff00ff,'gainsboro'=>0xdcdcdc,
        'ghostwhite'=>0xf8f8ff,'gold'=>0xffd700,'goldenrod'=>0xdaa520,'gray'=>0x808080,'green'=>0x008000,
        'greenyellow'=>0xadff2f,'grey'=>0x808080,'honeydew'=>0xf0fff0,'hotpink'=>0xff69b4,'indianred'=>0xcd5c5c,
        'indigo'=>0x4b0082,'ivory'=>0xfffff0,'khaki'=>0xf0e68c,'lavender'=>0xe6e6fa,'lavenderblush'=>0xfff0f5,
        'lawngreen'=>0x7cfc00,'lemonchiffon'=>0xfffacd,'lightblue'=>0xadd8e6,'lightcoral'=>0xf08080,
        'lightcyan'=>0xe0ffff,'lightgoldenrodyellow'=>0xfafad2,'lightgray'=>0xd3d3d3,'lightgreen'=>0x90ee90,
        'lightgrey'=>0xd3d3d3,'lightpink'=>0xffb6c1,'lightsalmon'=>0xffa07a,'lightseagreen'=>0x20b2aa,
        'lightskyblue'=>0x87cefa,'lightslategray'=>0x778899,'lightslategrey'=>0x778899,'lightsteelblue'=>0xb0c4de,
        'lightyellow'=>0xffffe0,'lime'=>0x00ff00,'limegreen'=>0x32cd32,'linen'=>0xfaf0e6,'magenta'=>0xff00ff,
        'maroon'=>0x800000,'mediumaquamarine'=>0x66cdaa,'mediumblue'=>0x0000cd,'mediumorchid'=>0xba55d3,
        'mediumpurple'=>0x9370db,'mediumseagreen'=>0x3cb371,'mediumslateblue'=>0x7b68ee,'mediumspringgreen'=>0x00fa9a,
        'mediumturquoise'=>0x48d1cc,'mediumvioletred'=>0xc71585,'midnightblue'=>0x191970,'mintcream'=>0xf5fffa,
        'mistyrose'=>0xffe4e1,'moccasin'=>0xffe4b5,'navajowhite'=>0xffdead,'navy'=>0x000080,'oldlace'=>0xfdf5e6,
        'olive'=>0x808000,'olivedrab'=>0x6b8e23,'orange'=>0xffa500,'orangered'=>0xff4500,'orchid'=>0xda70d6,
        'palegoldenrod'=>0xeee8aa,'palegreen'=>0x98fb98,'paleturquoise'=>0xafeeee,'palevioletred'=>0xdb7093,
        'papayawhip'=>0xffefd5,'peachpuff'=>0xffdab9,'peru'=>0xcd853f,'pink'=>0xffc0cb,'plum'=>0xdda0dd,
        'powderblue'=>0xb0e0e6,'purple'=>0x800080,'rebeccapurple'=>0x663399,'red'=>0xff0000,'rosybrown'=>0xbc8f8f,
        /*
         * On 21 June 2014, the CSS WG added the color RebeccaPurple to the Editor's Draft of the CSS4 Colors module,
         * to commemorate Eric Meyer's daughter Rebecca who died on 7 June 2014, her sixth birthday.
         * https://lists.w3.org/Archives/Public/www-style/2014Jun/0312.html
         */
        'royalblue'=>0x4169e1,'saddlebrown'=>0x8b4513,'salmon'=>0xfa8072,'sandybrown'=>0xf4a460,
        'seagreen'=>0x2e8b57,'seashell'=>0xfff5ee,'sienna'=>0xa0522d,'silver'=>0xc0c0c0,
        'skyblue'=>0x87ceeb,'slateblue'=>0x6a5acd,'slategray'=>0x708090,'slategrey'=>0x708090,
        'snow'=>0xfffafa,'springgreen'=>0x00ff7f,'steelblue'=>0x4682b4,'tan'=>0xd2b48c,'teal'=>0x008080,
        'thistle'=>0xd8bfd8,'tomato'=>0xff6347,'turquoise'=>0x40e0d0,'violet'=>0xee82ee,'wheat'=>0xf5deb3,
        'white'=>0xffffff,'whitesmoke'=>0xf5f5f5,'yellow'=>0xffff00,'yellowgreen'=>0x9acd32];




    public function __construct($color, $param1=null, $param2=null)
    {
        if(!is_integer($color) and empty($color)) {
            throw new \Exception('There is nothing here');
        }

        switch(gettype($color)) {
            case 'resource':
                if(get_resource_type($color)=='gd') {
                    /*
                     * $color is a gd image - in that case we also need the X, Y coordinates of the pixel
                     * we want to analyze
                     */
                    if((is_int($param1) and $param1>=0) and (is_int($param2) and $param2>=0)) {
                        $this->value = imagecolorat($color, $param1, $param2);
                        if($this->value === false) {
                            throw new \Exception('Pixel out of bounds');
                        }
                    } else {
                        /*
                         * missing or invalid $param1 and $param2
                         */
                        throw new \Exception('Missing pixel coordinates');
                    }

                } else {
                    throw new \Exception('Unknown resource of type '.get_resource_type($color));
                }
                break;

            case 'array':
                if(isset($color['r']) and isset($color['g']) and isset($color['b'])) {
                    $red=$color['r'];
                    $green=$color['g'];
                    $blue=$blue['b'];
                } else if(isset($color['red']) and isset($color['green']) and isset($color['blue'])) {
                    $red=$color['red'];
                    $green=$color['green'];
                    $blue=$color['blue'];
                } else {
                    throw new \Exception('I cannot make sense of this array, sorry...');
                }

                $this->value = $red*256*256 + $green * 256 + $blue;

                break;

            case 'string':
                $color=trim($color, '#\r\n\t ');

                if(ctype_xdigit($color)) {
                    if(strlen($color) == 3) {
                        $color = $color{0}.$color{0}.$color{1}.$color{1}.$color{2}.$color{2};
                    }

                    if(strlen($color) < 6) {
                        $color = str_pad($color, 6, '0', STR_PAD_LEFT);
                    }

                    if(strlen($color) == 6) {
                        $this->value = hexdec($color);
                    } else {
                        throw new \Exception('Not sure what this hex string is "'.$color.'", please let me know');
                    }
                } elseif(strpos($color, 'rgb')!==false or strpos($color, ',')!==false) {
                    // i hope this is some sort of rgb(r,g,b) kinda string, or maybe even rgba(r,g,b,a) - ignoring a
                    $color = trim(str_replace(array('rgb', 'rgba', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3 or count($color)==4) {
                            if(max($color)>255) {
                                throw new \Exception('If this is rgb, one of the channels is over 255...');
                            }
                            $this->value = $color[0] * 256*256 + $color[1] * 256 + $color[2];
                        }
                    }
                } else {
                    throw new \Exception('This is not hex, for support of other string formats, just let me know...');
                }



                break;

            case 'integer':
                /*
                 * check if the integer is a hex
                 */
                if($color>=0 and $color<=0xffffff) {
                    $this->value = $color;
                } else {
                    throw new \Exception('This integer is out of range');
                }

                break;

            case 'object':
                if(get_class($color) == 'Color') {
                    $this->value = $color->int;
                } else {
                    throw new \Exception('Cannot handle object of type '.get_class($color));
                }

                break;

            default:
                throw new \Exception('I really don\'t know what that color is');
                break;
        }
    }

    public function __toString() {
        return $this->getHex();
    }

    public function __get($param) {
        if($param == 'hex') {
            return $this->getHex();
        }

        if($param == 'rgb') {
            return $this->getRgb();
        }

        if($param == 'red') {
            return $this->getRed();
        }

        if($param == 'green') {
            return $this->getGreen();
        }

        if($param == 'blue') {
            return $this->getBlue();
        }

        if($param == 'int') {
            return $this->value;
        }

        if($param == 'name') {
            return $this->name;
        }


        $tr = debug_backtrace();
        trigger_error('Undefined property '.$param.' in '.$tr[0]['file'].' on line '.$tr[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($param, $value) {
        if($param == 'name') {
            $this->name = $value;
        }
    }

    public static function create($color, $param1=null, $param2=null)
    {
        return new Color($color, $param1, $param2);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getHex()
    {
        return '#'.str_pad(dechex($this->value), 6, '0', STR_PAD_LEFT);
    }

    public function getRed()
    {
        return $this->value >> 16 & 0xFF;
    }

    public function getGreen()
    {
        return $this->value >> 8 & 0xFF;
    }

    public function getBlue()
    {
        return $this->value & 0xFF;
    }

    public function getRgb()
    {
        $rgb['red']=$this->getRed();
        $rgb['blue']=$this->getBlue();
        $rgb['green']=$this->getGreen();
        return $rgb;
    }

    public function negate()
    {
        $this->value = 0xffffff - $this->value;
        return $this;
    }

    public function compare($color, $comparisonType = Color::COMPARE_FAST)
    {
        if(!$color instanceof Color) {
            $color = Color::create($color);
        }

        if($comparisonType <= 3)
        {
            return abs(pow($this->red - $color->red, $comparisonType))
            + abs(pow($this->green - $color->green, $comparisonType))
            + abs(pow($this->blue - $color->blue, $comparisonType));
        }
    }

    public function findSimilar($comparisonType = null, $collection = null)
    {
        $comparisonType = (is_null($comparisonType)) ? Color::COMPARE_FAST : $comparisonType;

        if(is_null($collection)) {
            $collection = $this->cssColors;
        }

        $minDiff = 0xffffff;
        $similarColor = 0x0;
        $colorName = null;

        foreach($collection as $name=>$color) {
            $color=Color::create($color);
            $diff = $this->compare($color, $comparisonType);
            if($diff < $minDiff) {
                $minDiff = $diff;
                $colorName = $name;
                $similarColor = $color;
            }
            //echo "Comparing ".$this->hex." to $name - ".$color->hex." - DIFF $diff / $minDiff MIN\n";
        }

        $similarColor -> name = $colorName;
        return $similarColor;
    }



}


### circular php unit test


## http://serennu.com/colour/rgbtohsl.php

## https://en.wikipedia.org/wiki/HSL_and_HSV

## http://lesscss.org/functions/

