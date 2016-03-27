<?php namespace ColorTools;

class Color
{
    private $colors = array();
    private $value = null;
    private $name = null;
    private $similarColor = null;

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
                        /*
                         * for images without an alpha channels, it's better to just use the int value returned by
                         * imagecoloarat(), instead of using imagecolorsforindex() functions for getting rgb values
                         * as it is considerably faster (much faster)
                         *
                         * the slow way is
                         * $this->setRgb(imagecolorsforindex($color, imagecolorat($color, $param1, $param2)));
                         * imagecolorat(...) will return something like
                         * [["red"]=> int(119), ["green"]=> int(123), ["blue"]=> int(180), ["alpha"]=> int(127)]
                         * but my support for alpha is non existent at the moment
                         *
                         */
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
                $color=trim($color, "# \r\n\t");

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
                } elseif(strpos($color, 'hsl')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('hsl', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3) {
                            $this->setHsl($color[0], $color[1], $color[2]);
                        } else {
                            throw new \Exception('Can\'t really understand this HSL string: '.$originalString);
                        }
                    }
                } elseif(strpos($color, 'hsv')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('hsv', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3) {
                            $this->setHsv($color[0], $color[1], $color[2]);
                        } else {
                            throw new \Exception('Can\'t really understand this HSV string: '.$originalString);
                        }
                    }
                } elseif(strpos($color, 'cmyk')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('cmyk', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==4) {
                            $this->setCmyk($color[0], $color[1], $color[2], $color[3]);
                        } else {
                            throw new \Exception('Can\'t really understand this CMYK string: '.$originalString);
                        }
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
                } elseif(in_array(strtolower($color), $this->cssColors)) {
                    $this->value = $this->cssColors[strtolower($color)];
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
                if(get_class($color) == 'ColorTools\Color') {
                    $this->value = $color->int;
                } else if(get_class($color) == 'Imagick') {
                    /*
                     * $color is a Imagick image - in that case we also need the X, Y coordinates of the pixel
                     * we want to analyze
                     */
                    if((is_int($param1) and $param1>=0) and (is_int($param2) and $param2>=0)) {
                        /*
                         * No way go get an int out of a ImagickPixel object.
                         * If you know an efficient and elegant way (without using getColour()), please let me know.
                         */
                        try {
                            $this->setRgb($color->getImagePixelColor($param1, $param2)->getColor());
                        } catch (\ImagickException $e) {
                            throw new \Exception('Problem getting the Imagick pixel: '.$e->getMessage());
                        }
                    } else {
                        /*
                         * missing or invalid $param1 and $param2
                         */
                        throw new \Exception('Missing pixel coordinates');
                    }
                } else if(get_class($color) == 'ImagickPixel') {
                    $this->setRgb($color->getColor());
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
        $param=strtolower($param);

        if($param == 'hex') {
            return $this->getHex();
        }

        if($param == 'safe' or $param == 'safeHex') {
            return $this->getSafeHex();
        }

        if($param == 'rgb') {
            $rgb = $this->getRgb();
            return 'rgb('.implode(', ', $rgb).')';
        }

        if($param == 'red' or $param == 'r') {
            return $this->getRed();
        }

        if($param == 'green' or $param == 'g') {
            return $this->getGreen();
        }

        if($param == 'blue' or $param == 'b') {
            return $this->getBlue();
        }

        if(in_array($param, ['grayscale', 'gray', 'mono'])) {
            return $this->getGrayscale();
        }

        if($param=='luma') {
            return round($this->getLuma()*100).'%';
        }

        if($param == 'hsl') {
            $hsl = $this->getHsl();
            $hsl['saturation']=round($hsl['saturation']*100, 2).'%';
            $hsl['lightness']=round($hsl['lightness']*100, 2).'%';
            return 'hsl('.implode(', ', $hsl).')';
        }

        if($param == 'hsv') {
            $hsv = $this->getHsv();
            $hsv['saturation']=round($hsv['saturation']*100, 2).'%';
            $hsv['value']=round($hsv['value']*100, 2).'%';
            return 'hsv('.implode(', ', $hsv).')';
        }

        if($param == 'cmyk') {
            $cmyk = $this->getCmyk();
            foreach($cmyk as $channel=>$value) {
                $cmyk[$channel] = round($value*100, 2).'%';
            }
            return 'cmyk('.implode(', ', $cmyk).')';
        }

        if($param == 'int') {
            return $this->value;
        }

        if($param == 'name') {
            if(is_null($this->name)) {
                if(is_null($this->similarColor)) {
                    $this->similarColor = $this->findSimilar();
                }
                $this->name = $this->similarColor->name;
            }
            return $this->name;
        }

        $tr = debug_backtrace();
        trigger_error('Undefined property '.$param.' in '.$tr[0]['file'].' on line '.$tr[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($param, $value) {
        $param=strtolower($param);

        if($param == 'name') {
            $this->name = $value;
        }

        if($param == 'r' or $param=='red') {
            $this->setRed($value);
        }

        if($param == 'g' or $param=='green') {
            $this->setGreen($value);
        }

        if($param == 'b' or $param=='blue') {
            $this->setBlue($value);
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

    public function getSafeHex()
    {
        return '#'.dechex(round($this->red/16)).dechex(round($this->green/16)).dechex(round($this->blue/16));
    }

    public function getRed()
    {
        return ($this->value >> 16) & 0xFF;
    }

    public function setRed($value)
    {
        if(is_numeric($value) and $value >= 0 and $value<256) {
            $this->value += (ceil($value) - $this->red) * 256 * 256;
            return $this;
        } else {
            throw new \Exception('There is something wrong with this red: '.print_r($value, true));
        }
    }

    public function getGreen()
    {
        return $this->value >> 8 & 0xFF;
    }

    public function setGreen($value)
    {
        if(is_numeric($value) and $value >= 0 and $value<256) {
            $this->value += (ceil($value) - $this->green) * 256;
            return $this;
        } else {
            throw new \Exception('There is something wrong with this green: '.print_r($value, true));
        }
    }

    public function getBlue()
    {
        return $this->value & 0xFF;
    }

    public function setBlue($value)
    {
        if(is_numeric($value) and $value >= 0 and $value<256) {
            $this->value += (ceil($value) - $this->blue);
            return $this;
        } else {
            throw new \Exception('There is something wrong with this blue: '.print_r($value, true));
        }
    }

    public function getRgb()
    {
        $rgb['red']=$this->getRed();
        $rgb['green']=$this->getGreen();
        $rgb['blue']=$this->getBlue();
        return $rgb;
    }

    public function setRgb($red = null, $green = null, $blue = null)
    {
        if(is_array($red) and isset($red['r']) and isset($red['g']) and isset($red['b'])) {
            $this->setRed($red['r']);
            $this->setGreen($red['g']);
            $this->setBlue($red['b']);
        } else if(is_array($red) and isset($red['red']) and isset($red['green']) and isset($red['blue'])) {
            $this->setRed($red['red']);
            $this->setGreen($red['green']);
            $this->setBlue($red['blue']);
        } else {
            if(!is_null($red)) {
                $this->setRed($red);
            }

            if(!is_null($green)) {
                $this->setGreen($green);
            }

            if(!is_null($blue)) {
                $this->setBlue($blue);
            }
        }

        return $this;
    }

    public function getGrayscale()
    {
        return ceil(($this->red + $this->green + $this->blue) / 3);
    }

    //http://www.rapidtables.com/convert/color/rgb-to-hsl.htm

    //http://www.rapidtables.com/convert/color/hsl-to-rgb.htm

    //http://www.easyrgb.com/?X=MATH

    public function getHsl()
    {
        $r = $this->r / 255;
        $g = $this->g / 255;
        $b = $this->b / 255;


        $cMax = max($r, $g, $b);
        $cMin = min($r, $g, $b);
        $cDif = $cMax - $cMin;

        $lightness = ($cMin + $cMax) / 2;

        if($cDif == 0) {
            $hue = 0;
            $saturation = 0;
        } else if($cMax == $r) {
            $hue = ($g - $b)/$cDif;
            if($hue < 0) {
                $hue = 6 + $hue;
            }
            $hue = deg2rad(60) * $hue;
        } else if($cMax == $g) {
            $hue = deg2rad(60) * (($b - $r)/$cDif + 2);
        } else if($cMax == $b) {
            $hue = deg2rad(60) * (($r - $g)/$cDif + 4);
        }

        if($cDif != 0) {
            $saturation = $cDif / (1 - abs(2 * $lightness - 1));
        }

        return ['hue'=> round(rad2deg($hue)), 'saturation'=>$saturation, 'lightness'=>$lightness];
    }

    public function setHsl($hue=null, $saturation=null, $lightness=null)
    {
        if(is_array($hue)) {
            /*
             * imagemagick uses for some reason the term "luminosity" instead of "ligthness"
             * check yourself:
             * http://php.net/manual/en/imagickpixel.gethsl.php
             * ..."Returns the HSL value in an array with the keys 'hue', 'saturation', and 'luminosity'"...
             * but, according to most people (https://en.wikipedia.org/wiki/HSL_and_HSV),
             * "HSL stands for hue, saturation, and lightness, and is also often called HLS".
             * I'm going to accept both lightness and luminosity. Tolerance is the key to a better world!
             */

            if(isset($hue['hue']) and isset($hue['saturation']) and (isset($hue['lightness']) or isset($hue['luminosity']))) {
                $hsl = $hue;
                if(isset($hue['lightness'])) {
                    $hsl['lightness'] = $hue['lightness'];
                } else if(isset($hue['lightness'])) {
                    $hsl['lightness'] = $hue['luminosity'];
                }

            } else if (isset($hue['h']) and isset($hue['s']) and isset($hue['l'])) {
                $hsl['hue'] = $hue['h'];
                $hsl['saturation'] = $hue['s'];
                $hsl['lightness'] = $hue['l'];
            } else {
                throw new \Exception('Don\'t understand this hsl array: '.print_r($hue, true));
            }
        } else if (!is_null($hue) and !is_null($saturation) and !is_null($lightness)) {
            $hsl['hue'] = $hue;
            $hsl['saturation'] = $saturation;
            $hsl['lightness'] = $lightness;
        } else {
            throw new \Exception('Can\'t get this HSL');
        }

        if(strpos($hsl['saturation'],'%') or $hsl['saturation'] > 1) {
            $hsl['saturation'] = trim($hsl['saturation'],"\r\n\t %") / 100;
        }

        if(strpos($hsl['lightness'],'%') or $hsl['lightness'] > 1) {
            $hsl['lightness'] = trim($hsl['lightness'],"\r\n\t %") / 100;
        }

        $c = (1 - abs(2*$hsl['lightness'] - 1)) * $hsl['saturation'];
        $x = $c * (1 - abs(fmod(deg2rad($hsl['hue']) / deg2rad(60), 2) - 1));
        $m = $hsl['lightness'] - $c/2;

        if($hsl['hue'] < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if($hsl['hue'] < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if($hsl['hue'] < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if($hsl['hue'] < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if($hsl['hue'] < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = round(($r+$m) * 255);
        $g = round(($g+$m) * 255);
        $b = round(($b+$m) * 255);

        $this->setRed($r);
        $this->setGreen($g);
        $this->setBlue($b);

        return $this;
    }

    public function getHsv()
    {
        $r = $this->r / 255;
        $g = $this->g / 255;
        $b = $this->b / 255;


        $cMax = max($r, $g, $b);
        $cMin = min($r, $g, $b);
        $cDif = $cMax - $cMin;

        if($cDif==0) {
            $hue = 0;
        } else if ($cMax == $r) {
            $hue = deg2rad(60) * fmod(($g - $b)/$cDif, 6);
            if($hue<0) {
                $hue+=2*M_PI;
            }
        } else if ($cMax == $g) {
            $hue = deg2rad(60) * ((($b - $r)/$cDif) + 2);
        } else if ($cMax == $b) {
            $hue = deg2rad(60) * ((($r - $g)/$cDif) + 4);
        }

        if($cMax==0) {
            $saturation=0;
        } else {
            $saturation = $cDif / $cMax;
        }

        $value = $cMax;

        return ['hue'=> round(rad2deg($hue)), 'saturation'=>$saturation, 'value'=>$value];
    }

    public function setHsv($hue=null, $saturation=null, $value=null)
    {
        if(is_array($hue)) {
            if(isset($hue['hue']) and isset($hue['saturation']) and isset($hue['value'])) {
                $hsv = $hue;
            } else if (isset($hue['h']) and isset($hue['s']) and isset($hue['v'])) {
                $hsv['hue'] = $hue['h'];
                $hsv['saturation'] = $hue['s'];
                $hsv['value'] = $hue['v'];
            } else {
                throw new \Exception('Don\'t understand this HSV array: '.print_r($hsv, true));
            }
        } else if (!is_null($hue) and !is_null($saturation) and !is_null($value)) {
            $hsv['hue'] = $hue;
            $hsv['saturation'] = $saturation;
            $hsv['value'] = $value;
        } else {
            throw new \Exception('Can\'t get this HSV');
        }

        if(strpos($hsv['saturation'],'%') or $hsv['saturation'] > 1) {
            $hsv['saturation'] = trim($hsv['saturation'],"\r\n\t %") / 100;
        }

        if(strpos($hsv['value'],'%') or $hsv['value'] > 1) {
            $hsv['value'] = trim($hsv['value'],"\r\n\t %") / 100;
        }

        $c = $hsv['value'] * $hsv['saturation'];
        $x = $c * (1 - abs(fmod($hsv['hue'] / 60, 2) - 1));
        $m = $hsv['value'] - $c;

        if($hsv['hue'] < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if($hsv['hue'] < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if($hsv['hue'] < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if($hsv['hue'] < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if($hsv['hue'] < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = round(($r+$m) * 255);
        $g = round(($g+$m) * 255);
        $b = round(($b+$m) * 255);

        $this->setRed($r);
        $this->setGreen($g);
        $this->setBlue($b);

        return $this;
    }

    public function getCmyk()
    {
        $rgb = $this->getRgb();

        $r = $rgb['red']/255;
        $g = $rgb['green']/255;
        $b = $rgb['blue']/255;
        $max = max($r, $g, $b);

        $k = 1 - $max;
        if($max==0) {
            $c = $m = $y = 0;
        } else {
            $c = ($max - $r) / $max;
            $m = ($max - $g) / $max;
            $y = ($max - $b) / $max;
        }


        return ['cyan'=>$c, 'magenta'=>$m, 'yellow'=>$y, 'black'=>$k];
    }

    public function setCmyk($cyan=null, $magenta=null, $yellow=null, $black=null)
    {
        if(is_array($cyan)) {
            if(isset($cyan['cyan']) and isset($cyan['magenta']) and isset($cyan['yellow'])) {
                $cmyk=$cyan;
            } else if (isset($cyan['c']) and isset($cyan['m']) and isset($cyan['y']) and isset($cyan['k'])) {
                $cmyk['cyan'] = $cyan['c'];
                $cmyk['magenta'] = $cyan['m'];
                $cmyk['yellow'] = $cyan['y'];
                $cmyk['black'] = $cyan['black'];
            } else {
                throw new \Exception('Don\'t understand this CMYK array: '.print_r($cyan, true));
            }
        } else if (!is_null($cyan) and !is_null($magenta) and !is_null($yellow) and !is_null($black)) {
            $cmyk['cyan'] = $cyan;
            $cmyk['magenta'] = $magenta;
            $cmyk['yellow'] = $yellow;
            $cmyk['black'] = $black;
        } else {
            throw new \Exception('Can\'t get this CMYK');
        }

        foreach($cmyk as $channel=>$value) {
            if(strpos($value, '%')!==false or $value>1) {
                $cmyk[$channel] = trim($value,"\r\n\t %")/100;
            } else {
                $cmyk[$channel] = trim($value,"\r\n\t ");
            }
        }

        $r = round(255 * (1 - $cmyk['cyan']) * (1 - $cmyk['black']));
        $g = round(255 * (1 - $cmyk['magenta']) * (1 - $cmyk['black']));
        $b = round(255 * (1 - $cmyk['yellow']) * (1 - $cmyk['black']));

        $this->setRgb($r, $g, $b);
        return $this;
    }

    public function getLuma()
    {
        $luma['r'] = 0.2126 * $this->r / 255;
        $luma['g'] = 0.7152 * $this->g / 255;
        $luma['b'] = 0.0722 * $this->b / 255;
        return array_sum($luma);
    }

    public function invert()
    {
        return $this->rgbTransformation(function($value) {
            return 1 - $value;
        });
    }

    public function complement()
    {
        return $this->spin(180);
    }

    public function mix($secondColor, $weight=0.5)
    {
        if($weight>=1) { //not sure if no one will ever mix 100%
            $weight/=100;
        }

        $weight=min($weight, 1);

        if($weight<0) {
            $weight=0;
        }

        return $this->rgbTransformation(function($value, $secondValue) use ($weight) {
            return $value * (1 - $weight) + $secondValue * $weight;
        }, $secondColor);
    }

    public function tint($weight=0.1)
    {
        return $this->mix(0xffffff, $weight);
    }

    public function shade($weight=0.1)
    {
        return $this->mix(0, $weight);
    }

    public function grayscale()
    {
        return $this->desaturate(100);
    }

    public function spin($hueAngle=0)
    {
        $hsl = $this->getHsl();
        $hsl['hue'] += $hueAngle;
        while($hsl['hue'] < 0) {
            $hsl['hue']+= 360;
        }
        $hsl['hue'] = $hsl['hue'] % 360;
        $this->setHsl($hsl);
        return $this;
    }

    public function saturate($saturationAdjustement=0)
    {
        $hsl = $this->getHsl();
        if($saturationAdjustement>=1 or $saturationAdjustement<=-1) { //not sure if no one will ever [de]saturate 100%
            $saturationAdjustement/=100;
        }
        $hsl['saturation'] += $saturationAdjustement;

        if($hsl['saturation']>1) {
            $hsl['saturation']=1;
        }
        if($hsl['saturation']<0) {
            $hsl['saturation']=0;
        }

        $this->setHsl($hsl);
        return $this;
    }

    public function desaturate($saturationAdjustement=0)
    {
        return $this->saturate(0-$saturationAdjustement);
    }

    public function lighten($lightnessAdjustement=0)
    {
        $hsl = $this->getHsl();
        if($lightnessAdjustement>=1 or $lightnessAdjustement<=-1) { //not sure if no one will ever [de]lighten 100%
            $lightnessAdjustement/=100;
        }
        $hsl['lightness'] += $lightnessAdjustement;

        if($hsl['lightness']>1) {
            $hsl['lightness']=1;
        }
        if($hsl['lightness']<0) {
            $hsl['lightness']=0;
        }

        $this->setHsl($hsl);
        return $this;
    }

    public function darken($lightnessAdjustement=0)
    {
        return $this->lighten(0-$lightnessAdjustement);
    }

    public function rgbTransformation($transformation, $secondColor=null)
    {
        if(!is_null($secondColor)) {
            $secondColor=Color::create($secondColor);
        }
        foreach(['red', 'green', 'blue'] as $channel) {
            if(!is_null($secondColor)) {
                $value = $transformation($this -> $channel / 255, $secondColor -> $channel / 255, $channel);
            } else {
                $value = $transformation($this -> $channel / 255, $channel);
            }
            $value = min($value, 1);
            if($value<0) {
                $value = 0;
            }
            $this -> $channel = round($value * 255);
        }
        return $this;
    }

    public function multiply($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return $value * $secondValue;
        }, $secondColor);
    }

    public function screen($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return $value + $secondValue - ($value * $secondValue);
        }, $secondColor);
    }

    public function overlay($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            $value *= 2;

            if($value<=1) {
                return $value * $secondValue;
            } else {
                $value -= 1;
                return $value + $secondValue - ($value * $secondValue);
            }
        }, $secondColor);
    }

    public function softlight($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            $d = 1;
            $e = $value;

            if($secondValue > 0.5) {
                $e = 1;
                if($value > 0.25) {
                    $d = sqrt($value);
                } else {
                    $d = ((16 * $value - 12) * $value + 4) * $value;
                }
            }
            return $value - (1 - 2 * $secondValue) * $e * ($d - $value);
        }, $secondColor);
    }

    public function hardlight($secondColor)
    {
        return Color::create($secondColor)->overlay($this);
    }

    public function difference($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return abs($value - $secondValue);
        }, $secondColor);
    }

    public function exclusion($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return $value + $secondValue - 2 * $value * $secondValue;
        }, $secondColor);
    }

    public function average($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return ($value + $secondValue) / 2;
        }, $secondColor);
    }

    public function negate($secondColor)
    {
        return $this->rgbTransformation(function($value, $secondValue) {
            return 1 - abs($value + $secondValue - 1);
        }, $secondColor);
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

    //https://www.w3.org/TR/WCAG20/#contrast-ratiodef
    public function findConstrast($darkColor=0x0, $lightColor=0xffffff, $threshold=50)
    {
        $darkColor=Color::create($darkColor);
        $lightColor=Color::create($lightColor);

        $darkContrast =  ($this->getLuma() > $darkColor->getLuma()) ?
                            ($this->getLuma() + 0.05) / ($darkColor->getLuma() + 0.05) :
                            ($darkColor->getLuma() + 0.05) / ($this->getLuma() + 0.05);

        $lightContrast =  ($this->getLuma() > $lightColor->getLuma()) ?
            ($this->getLuma() + 0.05) / ($lightColor->getLuma() + 0.05) :
            ($lightColor->getLuma() + 0.05) / ($this->getLuma() + 0.05);

        $lightContrast += $threshold/21;
        $darkContrast -= $threshold/21;

        if($darkContrast > $lightContrast) {
            return $darkColor;
        } else {
            return $lightColor;
        }
    }

    public function findSimilar($comparisonType = null, $collection = null, $avoidBlacks=false)
    {
        $comparisonType = (is_null($comparisonType)) ? Color::COMPARE_GREAT : $comparisonType;

        if(is_null($collection)) {
            $collection = $this->cssColors;
        }

        if($avoidBlacks)
        {
            if($this->getLuma()>0.04 and $this->getLuma()<0.16) {
                $this->lighten(ceil(5+$this->getLuma()*100));
            }
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
        }

        $similarColor = Color::create($similarColor);
        $similarColor -> name = $colorName;
        return $similarColor;
    }



}


### circular php unit test


## http://serennu.com/color/rgbtohsl.php

## https://en.wikipedia.org/wiki/HSL_and_HSV

## http://lesscss.org/functions/

