<?php

error_reporting(E_ALL); ini_set('display_errors', 1);

if(!file_exists('../vendor/autoload.php')) {
    throw new \Exception('Try running composer update first in '.realpath('..'));
} else {
    require_once('../vendor/autoload.php');
}

use ColorTools\Palette as Palette;
use ColorTools\Color as Color;
use ColorTools\Image as Image;

function showPalette($palette = Palette::PALETTE_COLOR_TOOLS)
{
    $palette = new Palette(Palette::PALETTE_COLOR_TOOLS);

    echo '<table><tr>';
    foreach($palette->getPalette() as $color) {
        $color = Color::create($color)->hex;
        echo '<td title="$color" style="width:24px;height:10px;background-color:'.$color.';"></td>';
    }
    echo '</tr></table>';
}

function showImageAndPalette($imagePath, $precision=Palette::ADAPTIVE_PRECISION)
{
    $image = new Image($imagePath);
    $start = microtime(true);
    $palette = $image->getColors(Palette::PALETTE_COLOR_TOOLS, Color::COMPARE_GREAT, $precision, 3);

    if($precision==Palette::ADAPTIVE_PRECISION) {
        $precision = 'adaptive precision ('.$palette->precision.')';
    } else {
        $precision.= ' precision';
    }

    $imageSrc = $image->getImageSrc();
    $imagePath = $image->getImagePath();
    if(substr($imagePath, 0, 7)=='http://') {
        $imagePath = substr($imagePath, 7);
    }

    if(is_null($imagePath)) {
        $imagePath = '['.$image->getImageType().' source]';
    }


    echo '<div style="border:1px solid black;box-shadow:0 0 10px rgba(0,0,0, .5);margin:15px;float:left;">
    <p style="margin:3px;text-indent:7px;font-size:12px;">'.$imagePath.' with '.$precision.' - got '.
        count($palette->colors).' colors in '.round($palette->colorsTime, 3).'s - brightness - '.
        round($palette->luma*100).'%</p>
    <img src="'.$imageSrc.'" width="600" /><br/>
    <table style="border-collapse:collapse;width:600px;height:25px;font-size:12px;line-height:12px;margin:0;padding:0;">
        <tr>';
    foreach($palette->colors as $hex=>$coverage) {
        if($coverage>2) {
            echo '<td style="color:white;text-shadow:0 0 5px black;text-align:center;background-color:'.$hex.';
                    width:'.$coverage.'%;" title="%'.number_format($coverage,1).'">'
                    .number_format($coverage,1).
                '</td>';
        }
    }

    echo '</tr>
    </table></div>';
}

?><html>
<body>
<?php
showPalette();

$testUrl ='http://'.$_SERVER['SERVER_NAME'];
$testUrl.=substr($_SERVER['REQUEST_URI'],0, strrpos($_SERVER['REQUEST_URI'], '/')+1).'../samples/test5.jpg';

showImageAndPalette('../samples/test.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette(file_get_contents('../samples/test2.jpg'), Palette::ADAPTIVE_PRECISION);
showImageAndPalette(imagecreatefromjpeg('../samples/test3.jpg'), Palette::ADAPTIVE_PRECISION);
showImageAndPalette($testUrl, Palette::ADAPTIVE_PRECISION);
showImageAndPalette(new Imagick('../samples/test5.jpg'), Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test6.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test7.jpg', Palette::ADAPTIVE_PRECISION);

?>


</body>
</html>