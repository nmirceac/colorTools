<?php namespace ColorTools;

class Store
{
    const ADAPTIVE_PRECISION = -1;
    const DEFAULT_MIN_COVERAGE = 4;

    public $palette = null;
    private $luma = null;
    private $histogram = null;
    public $precision = null;
    private $colors = null;
    private $time = null;
    public $analysisOptions = null;
    private $width = null;
    private $height = null;
    private $sampledWidth = null;
    private $sampledHeight = null;
    private $sampledPixels = [];
    private $similarColorPixels = [];

    public function __construct($color=0, $param1=null, $param2=null)
    {
        if(!is_integer($color) and empty($color)) {
            throw new Exception('There is nothing here');
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
                            throw new Exception('Pixel out of bounds');
                        }
                    } else {
                        /*
                         * missing or invalid $param1 and $param2
                         */
                        throw new Exception('Missing pixel coordinates');
                    }
                } else {
                    throw new Exception('Unknown resource of type '.get_resource_type($color));
                }
                break;

            case 'array':
                if(isset($color['r']) and isset($color['g']) and isset($color['b'])) {
                    $red=$color['r'];
                    $green=$color['g'];
                    $blue=$color['b'];
                } else if(isset($color['red']) and isset($color['green']) and isset($color['blue'])) {
                    $red=$color['red'];
                    $green=$color['green'];
                    $blue=$color['blue'];
                } else if(isset($color[0]) and isset($color[1]) and isset($color[2]) and count($color) <= 4) {
                    $red=$color[0];
                    $green=$color[1];
                    $blue=$color[2];
                } else {
                    throw new Exception('I cannot make sense of this array, sorry...');
                }

                $this->value = 0;
                $this->setRed($red);
                $this->setGreen($green);
                $this->setBlue($blue);

                break;

            case 'string':
                $color=trim($color, "# \r\n\t");

                if(ctype_xdigit($color)) {
                    $this->setHex($color);
                } else if(strpos($color, 'hsl')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('hsla', 'hsl', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3 or count($color)==4) {
                            $this->setHsl($color[0], $color[1], $color[2]);
                        } else {
                            throw new Exception('Can\'t really understand this HSL string: '.$originalString);
                        }
                    }
                } else if(strpos($color, 'hsv')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('hsva', 'hsv', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3 or count($color)==4) {
                            $this->setHsv($color[0], $color[1], $color[2]);
                        } else {
                            throw new Exception('Can\'t really understand this HSV string: '.$originalString);
                        }
                    }
                } else if(strpos($color, 'cmyk')!==false and strpos($color, ',')!==false) {
                    $originalString = $color;
                    $color = trim(str_replace(array('cmyk', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==4) {
                            $this->setCmyk($color[0], $color[1], $color[2], $color[3]);
                        } else {
                            throw new Exception('Can\'t really understand this CMYK string: '.$originalString);
                        }
                    }
                } else if(strpos($color, 'rgb')!==false or strpos($color, ',')!==false) {
                    // i hope this is some sort of rgb(r,g,b) kinda string, or maybe even rgba(r,g,b,a) - ignoring a
                    $color = trim(str_replace(array('rgba', 'rgb', '(', ')'), '', $color), "\r\n\t ");
                    if(strpos($color, ',')!==false) {
                        $color = str_replace(' ', '', $color);
                        $color = explode(',', $color);
                        if(count($color)==3 or count($color)==4) {
                            if(max($color)>255) {
                                throw new Exception('If this is rgb, one of the channels is over 255...');
                            }
                            $this->value = $color[0] * 256*256 + $color[1] * 256 + $color[2];
                        }
                    }
                } else if(isset($this->cssColors[strtolower($color)])) {
                    $this->name = $color;
                    $this->value = $this->cssColors[strtolower($color)];
                } else if(isset($this->allColors[$color])) {
                    $color = $this->allColors[$color];
                    $this->name = $color;
                    if(is_array($color)) {
                        $this->value = $color[0];
                        if(isset($color[1]['url'])) {
                            $this->details['url'] = $color[1]['url'];
                        }
                    } else {
                        $this->value = $color;
                    }

                } else {
                    throw new Exception('I really don\'t know what this "'.print_r($color, true).'" is.');
                }



                break;

            case 'integer':
                if((is_int($param1) and $param1>=0) and (is_int($param2) and $param2>=0)) {
                    $this->setRgb($color, $param1, $param2);
                } else {
                    $this->setHex($color);
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
                            throw new Exception('Problem getting the Imagick pixel: '.$e->getMessage());
                        }
                    } else {
                        /*
                         * missing or invalid $param1 and $param2
                         */
                        throw new Exception('Missing pixel coordinates');
                    }
                } else if(get_class($color) == 'ImagickPixel') {
                    $this->setRgb($color->getColor());
                } else {
                    throw new Exception('Cannot handle object of type '.get_class($color));
                }

                break;

            default:
                throw new Exception('I really don\'t know what that color is');
                break;
        }
    }

}