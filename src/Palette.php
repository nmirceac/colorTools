<?php namespace ColorTools;

class Palette
{
    private $comparisonType = NULL;

    const ADAPTIVE_PRECISION   =-1;

    const PALETTE_COLOR_TOOLS  = 0;
    const PALETTE_BRIAN_MCDO   = 1;
    const PALETTE_RGB3         = 2;
    const PALETTE_NES          = 3;
    const PALETTE_APPLE        = 4;

    const DEFAULT_MIN_COVERAGE = 4;

    private $palette = null;

    private $colorTools = array(
        0x000000,0x000091,0x0000ff,0x910000,0x910091,0x9100ff,0xff0000,0xff0091,0xff00ff,
        0x009100,0x009191,0x0091ff,0x919100,0x919191,0x9191ff,0xff9100,0xff9191,0xff91ff,
        0x00ff00,0x00ff91,0x00ffff,0x91ff00,0x91ff91,0x91ffff,0xffff00,0xffff91,0xffffff,
        0xea4c88,0x993399,0x663399
    );

    /*
     * https://en.wikipedia.org/wiki/List_of_monochrome_and_RGB_palettes#3-level_RGB
     */

    private $rgb3 = array(
        0x000000,0x000091,0x0000ff,0x910000,0x910091,0x9100ff,0xff0000,0xff0091,0xff00ff,
        0x009100,0x009191,0x0091ff,0x919100,0x919191,0x9191ff,0xff9100,0xff9191,0xff91ff,
        0x00ff00,0x00ff91,0x00ffff,0x91ff00,0x91ff91,0x91ffff,0xffff00,0xffff91,0xffffff
    );

    /*
     * https://en.wikipedia.org/wiki/List_of_video_game_console_palettes#Famicom.2FNES
     */

    private $nes = array(
        0x007c7c,0x0000fc,0x0000bc,0x0028bc,0x000084,0x00f878,0x00f8b8,0x00f8d8,0x00d8d8,
        0x007800,0x006800,0x005800,0x004058,0x000000,0x00bcbc,0x0078f8,0x0058f8,0x0044fc,
        0x0000cc,0x000058,0x003800,0x005c10,0x007c00,0x00b800,0x00a800,0x00a844,0x008888,
        0x00f8f8,0x00bcfc,0x0088fc,0x005898,0x007858,0x00a044,0x00f818,0x00d854,0x00f898,
        0x00e8d8,0x007878,0x00fcfc,0x00e4fc,0x00b8f8,0x00a4c0,0x00d0b0,0x00e0a8,0x00d878
    );

    /*
     * https://en.wikipedia.org/wiki/List_of_software_palettes#Apple_Macintosh_default_16-color_palette
     */

    private $apple = array(
        0xffffff,0xffff00,0xff6600,0xdd0000,0xff0099,0x330099,0x0000cc,0x0099ff,
        0x00aa00,0x006600,0x663300,0x996633,0xbbbbbb,0x888888,0x444444,0x000000
    );

    /*
     *  https://github.com/brianmcdo/ImagePalette
     */

    private $BrianMcdoPalette = array(
        0x660000,0x990000,0xcc0000,0xcc3333,0xea4c88,0x993399,0x663399,0x333399,0x0066cc,
        0x0099cc,0x66cccc,0x77cc33,0x669900,0x336600,0x666600,0x999900,0xcccc33,0xffff00,
        0xffcc33,0xff9900,0xff6600,0xcc6633,0x996633,0x663300,0x000000,0x999999,0xcccccc,
        0xffffff, 0xe7d8b1, 0xfdadc7,0x424153, 0xabbcda, 0xf5dd01
    );

    public $luma = null;
    public $histogram = null;
    public $precision = null;
    public $colors =  null;
    public $colorsTime =  null;

    public function __construct($paletteType = null, $comparisonType = null)
    {
        $paletteType = (is_null($paletteType)) ? Palette::PALETTE_BRIAN_MCDO : $paletteType;
        $this->comparisonType = (is_null($comparisonType)) ? Color::COMPARE_GREAT : $comparisonType;

        if($paletteType==Palette::PALETTE_COLOR_TOOLS) {
            $this->palette = $this->colorTools;
        }

        if($paletteType==Palette::PALETTE_BRIAN_MCDO) {
            $this->palette = $this->BrianMcdoPalette;
        }

        if($paletteType==Palette::PALETTE_RGB3) {
            $this->palette = $this->rgb3;
        }

        if($paletteType==Palette::PALETTE_NES) {
            $this->palette = $this->nes;
        }

        if($paletteType==Palette::PALETTE_APPLE) {
            $this->palette = $this->apple;
        }


        if(is_null($this->palette)) {
            throw new \Exception('Invalid palette selected');
        }
    }

    public function getPalette()
    {
        return $this->palette;
    }

    public function getColors(Image $image, $precision=null, $minCoverage = null)
    {
        $timeStart = microtime(true);
        $precision = (is_null($precision)) ? Palette::ADAPTIVE_PRECISION : $precision;
        $minCoverage = (is_null($minCoverage)) ? Palette::DEFAULT_MIN_COVERAGE : $minCoverage;

        $pixels = $image->width * $image->height;

        if($precision == Palette::ADAPTIVE_PRECISION) {
            $precision = intval(ceil(sqrt($pixels)/80));
        }

        $this->precision = $precision;

        $this->sampledPixels = floor($image->width/$precision) * floor($image->height/$precision);
        $luma = 0;

        $palette = $this->palette;
        $paletteQuantities = array();

        $histogram['a'] = array_fill(0, 256, 0);
        $histogram['r'] = array_fill(0, 256, 0);
        $histogram['g'] = array_fill(0, 256, 0);
        $histogram['b'] = array_fill(0, 256, 0);

        for($x=0; $x<=($image->width - $precision); $x+=$precision) {
            for($y=0; $y<=($image->height - $precision); $y+=$precision) {
                $color = Color::create($image->getImageObject(), $x, $y);

                $luma += $color->getLuma();
                $average = round(($color->r + $color->g * 2 + $color->b) / 4);
                $histogram['a'][$average]++;
                //$histogram['a'][round(array_sum($color->getRgb()) / 3)]++;
                $histogram['r'][$color->r]++;
                $histogram['g'][$color->g]++;
                $histogram['b'][$color->b]++;

                $color = $color->findSimilar($this->comparisonType, $palette, true)->hex;
                if(!isset($paletteQuantities[$color])) {
                    $paletteQuantities[$color] =pow($precision,2);
                } else {
                    $paletteQuantities[$color]+=pow($precision,2);
                }
            }
        }

        $luma /= $this->sampledPixels;
        $this->luma = $luma;

        foreach($histogram as $channel=>$h) {

            //smoothing edges
            $max = max(array_slice($h, 1, -1));
            $scale = 1 / $max;

            foreach($h as $color=>$value) {
                $h[$color] = min($scale * $value, 1);
            }

            $histogram[$channel] = $h;
        }

        $this->histogram = $histogram;

        asort($paletteQuantities, SORT_NUMERIC);
        $paletteQuantities = array_reverse($paletteQuantities);

        foreach($paletteQuantities as $hex=>$value) {
            $paletteQuantities[$hex] = round($value / $pixels * 100, 2);
            //convert the coverage to % out of total number of pixels

            if($minCoverage and $paletteQuantities[$hex] < $minCoverage) {
                unset($paletteQuantities[$hex]);
            }
        }

        $this->colors = $paletteQuantities;
        $this->colorsTime = microtime(true) - $timeStart;
        return $this;
    }

    public static function getHistogramSrc($histogramArray, $color='black')
    {
        return 'data:image/svg+xml;base64, '.base64_encode(self::buildHistogram($histogramArray, $color));
    }

    public static function buildHistogram($histogramArray, $color='black')
    {
        $maxHeight = 100;
        $barWidth = 1;
        $max=max($histogramArray);
        $scale=$maxHeight / $max;
        $content ='<?xml version="1.0" standalone="no"?>';
        $content.='<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
        $content.='<svg width="'.($barWidth*256).'" height="'.$maxHeight.'" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">';
        $content.='<desc>Histogram</desc>';
        foreach($histogramArray as $r => $val) {
            $content.='<rect style="fill:'.$color.';fill-opacity:0.8;" x="'.($r * $barWidth).'" y="'.($maxHeight-$scale * $val).'" width="'.($barWidth).'" height="'.($scale * $val).'"/>';
        }
        $content.='</svg>';
        return $content;
    }
}


/*

$string = '
<td style="color:white; background:rgb(124,124,124);"></td>
<td style="color:white; background:rgb(0,0,252);"></td>
<td style="color:white; background:rgb(0,0,188);"></td>
<td style="color:white; background:rgb(68,40,188);"></td>
<td style="color:white; background:rgb(148,0,132);"></td>
<td style="color:white; background:rgb(168,0,32);"></td>
<td style="color:white; background:rgb(168,16,0);"></td>
<td style="color:white; background:rgb(136,20,0);"></td>
<td style="color:white; background:rgb(80,48,0);"></td>
<td style="color:white; background:rgb(0,120,0);"></td>
<td style="color:white; background:rgb(0,104,0);"></td>
<td style="color:white; background:rgb(0,88,0);"></td>
<td style="color:white; background:rgb(0,64,88);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
</tr>
<tr>
<td>10h</td>
<td style="color:white; background:rgb(188,188,188);"></td>
<td style="color:white; background:rgb(0,120,248);"></td>
<td style="color:white; background:rgb(0,88,248);"></td>
<td style="color:white; background:rgb(104,68,252);"></td>
<td style="color:white; background:rgb(216,0,204);"></td>
<td style="color:white; background:rgb(228,0,88);"></td>
<td style="color:white; background:rgb(248,56,0);"></td>
<td style="color:white; background:rgb(228,92,16);"></td>
<td style="color:white; background:rgb(172,124,0);"></td>
<td style="color:white; background:rgb(0,184,0);"></td>
<td style="color:white; background:rgb(0,168,0);"></td>
<td style="color:white; background:rgb(0,168,68);"></td>
<td style="color:white; background:rgb(0,136,136);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
</tr>
<tr>
<td>20h</td>
<td style="color:white; background:rgb(248,248,248);"></td>
<td style="color:white; background:rgb(60,188,252);"></td>
<td style="color:white; background:rgb(104,136,252);"></td>
<td style="color:white; background:rgb(152,120,248);"></td>
<td style="color:white; background:rgb(248,120,248);"></td>
<td style="color:white; background:rgb(248,88,152);"></td>
<td style="color:white; background:rgb(248,120,88);"></td>
<td style="color:white; background:rgb(252,160,68);"></td>
<td style="color:white; background:rgb(248,184,0);"></td>
<td style="color:white; background:rgb(184,248,24);"></td>
<td style="color:white; background:rgb(88,216,84);"></td>
<td style="color:white; background:rgb(88,248,152);"></td>
<td style="color:white; background:rgb(0,232,216);"></td>
<td style="color:white; background:rgb(120,120,120);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
</tr>
<tr>
<td>30h</td>
<td style="color:white; background:rgb(252,252,252);"></td>
<td style="color:white; background:rgb(164,228,252);"></td>
<td style="color:white; background:rgb(184,184,248);"></td>
<td style="color:white; background:rgb(216,184,248);"></td>
<td style="color:white; background:rgb(248,184,248);"></td>
<td style="color:white; background:rgb(248,164,192);"></td>
<td style="color:white; background:rgb(240,208,176);"></td>
<td style="color:white; background:rgb(252,224,168);"></td>
<td style="color:white; background:rgb(248,216,120);"></td>
<td style="color:white; background:rgb(216,248,120);"></td>
<td style="color:white; background:rgb(184,248,184);"></td>
<td style="color:white; background:rgb(184,248,216);"></td>
<td style="color:white; background:rgb(0,252,252);"></td>
<td style="color:white; background:rgb(216,216,216);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
<td style="color:white; background:rgb(0,0,0);"></td>
';

$colors=array();

foreach(explode(PHP_EOL, $string) as $row) {
    if(strlen($row)<32) {
        continue;
    }

    $color = substr(substr($row, 35), 0, -8);
    $color = Color::create($color);
    $colors[]="0x".strtolower(substr($color,-6));
}

echo '['.implode(',',array_unique($colors)).']';


*/


/*
 * for testing
 */

/*
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
echo PHP_EOL.'||';
echo PHP_EOL.'||';
echo PHP_EOL;
echo $color7->compare(336699);

*/