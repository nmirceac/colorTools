<?php

class Color
{
    private $colors = array();
    private $value = null;

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

                if(strlen($color) == 3) {
                    $color = $color{0}.$color{0}.$color{1}.$color{1}.$color{2}.$color{2};
                }

                if(strlen($color) == 4) {
                    $color.= '00';
                }

                if(strlen($color) == 6) {
                    $this->value = hexdec($color);
                } else {
                    throw new \Exception('Not sure what this string is "'.$color.'" - but I would really like to know');
                }

                break;

            case 'integer':
                /*
                 * check if the integer is a hex
                 */

                if(ctype_xdigit($color)) {
                    if($color>=0 and $color<=0xffffff) {
                        $this->value = $color;
                    } else {
                        throw new \Exception('This integer is out of range');
                    }
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

        if($param == 'int') {
            return $this->value;
        }


        $tr = debug_backtrace();
        trigger_error('Undefined property '.$param.' in '.$tr[0]['file'].' on line '.$tr[0]['line'], E_USER_NOTICE);
        return null;
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
        return '#'.str_pad(dechex($this->value), 6, '0');
    }

    public function getRgb()
    {
        $rgb['red']=$this->value >> 16 & 0xFF;
        $rgb['blue']=$this->value >> 8 & 0xFF;
        $rgb['green']=$this->value & 0xFF;
        return $rgb;
    }

    public function negate()
    {
        $this->value = 0xffffff - $this->value;
        return $this;
    }

}


### circular php unit test


## http://serennu.com/colour/rgbtohsl.php

## https://en.wikipedia.org/wiki/HSL_and_HSV

## http://lesscss.org/functions/

