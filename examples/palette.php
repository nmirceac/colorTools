<?php
require_once('autoloader.php');

use ColorTools\Palette as Palette;
use ColorTools\Color as Color;
use ColorTools\Image as Image;
use ColorTools\Analyze as Analyze;
use ColorTools\Histogram as Histogram;

function showPalette($palette = Palette::PALETTE_COLOR_TOOLS)
{
    $palette = new Palette(Palette::PALETTE_COLOR_TOOLS);

    echo '<table><tr>';
    foreach($palette->collection as $color) {
        $color = Color::create($color)->hex;
        echo '<td title="$color" style="width:24px;height:10px;background-color:'.$color.';"></td>';
    }
    echo '</tr></table>';
}

function showImageAndPalette($imagePath, $precision=Analyze::ADAPTIVE_PRECISION)
{
    $image = new Image($imagePath);
    $analysis = $image->getAnalysis(['palette'=>Palette::PALETTE_COLOR_TOOLS, 'comparisonType'=>Color::COMPARE_GREAT, 'precision'=>$precision, 'minCoverage'=>3]);


    if($precision==Analyze::ADAPTIVE_PRECISION) {
        $precision = 'adaptive precision ('.$analysis->precision.')';
    } else {
        $precision.= ' precision';
    }

    $imagePath = $image->getImagePath();
    if(substr($imagePath, 0, 7)=='http://') {
        $imagePath = substr($imagePath, 7);
    }

    if(is_null($imagePath)) {
        $imagePath = '['.$image->getImageType().' source]';
    }

    //faking properties usage to add them to the total operation time
    $analysis->luma;
    $analysis->histogram;
    $sampledPixelsImage = $analysis->getSampledPixelsImage();
    $similarColorImage = $analysis->getSimilarColorImage();

    echo '<div style="border:1px solid black;box-shadow:0 0 10px rgba(0,0,0, .5);margin:15px;float:left;">
    <p style="margin:3px;text-indent:7px;font-size:12px;">'.$imagePath.' with '.$precision.' - got '.
        count($analysis->colors).' colors in '.round(array_sum($analysis->time), 3).'s - brightness - '.
        round($analysis->luma*100).'%</p>
    <img src="'.$image->getImageSrc().'" width="600" /><br/>
    <table style="border-collapse:collapse;width:600px;height:25px;font-size:12px;line-height:12px;margin:0;padding:0;">
        <tr>';
    foreach($analysis->colors as $hex=>$coverage) {
        if($coverage>2) {
            echo '<td style="color:white;text-shadow:0 0 5px black;text-align:center;background-color:'.$hex.';
                    width:'.$coverage.'%;" title="%'.number_format($coverage,1).'">'
                    .number_format($coverage,1).
                '</td>';
        }
    }

    echo '</tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:600px;text-align:center;margin:0;padding:0;">
    <tr>
    <td><img src="'.$analysis->histogram->getSrc('a').'" /></td>
    <td><img src="'.$analysis->histogram->getSrc('r').'" /></td>
    </tr>
    <tr>
    <td><img src="'.$analysis->histogram->getSrc('g').'" /></td>
    <td><img src="'.$analysis->histogram->getSrc('b').'" /></td>
    </tr>
    <tr>
    <td><img src="'.$analysis->histogram->getSrc('c').'" /></td>
    <td><img src="'.$analysis->histogram->getSrc('l').'" /></td>
    </tr>
    </table></div>';
}
unset($analysis);
unset($image);
?><html>
<body>
<?php
showPalette();

$testUrl ='http://'.$_SERVER['SERVER_NAME'];
$testUrl.=substr($_SERVER['REQUEST_URI'],0, strrpos($_SERVER['REQUEST_URI'], '/')+1).'../samples/test4.jpg';

showImageAndPalette('../samples/test.jpg');
showImageAndPalette(file_get_contents('../samples/test2.jpg'));
showImageAndPalette(imagecreatefromjpeg('../samples/test3.jpg'));
showImageAndPalette($testUrl);
showImageAndPalette(new Imagick('../samples/test5.jpg'));
showImageAndPalette('../samples/test6.jpg');
showImageAndPalette('../samples/test7.jpg');

//echo '<p>'.round(memory_get_usage()/1024/1024, 2).'</p>';
//echo '<p>'.round(memory_get_peak_usage()/1024/1024, 2).'</p>';
?>


</body>
</html>