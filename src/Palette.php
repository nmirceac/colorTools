<?php namespace ColorTools;

class Palette
{
    const PALETTE_COLOR_TOOLS  = 0;
    const PALETTE_BRIAN_MCDO   = 1;
    const PALETTE_RGB3         = 2;
    const PALETTE_NES          = 3;
    const PALETTE_APPLE        = 4;


    private $index = 0;
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

    public function __construct($paletteType = null)
    {
        $paletteType = (is_null($paletteType)) ? Palette::PALETTE_COLOR_TOOLS : $paletteType;

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

    public function __get($param)
    {
        $param = strtolower($param);

        if ($param == 'collection') {
            return $this->getCollection();
        }
    }

    public function getCollection()
    {
        return $this->palette;
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