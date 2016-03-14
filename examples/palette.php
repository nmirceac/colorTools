<?php require_once('../src/Palette.php');

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
    $start = microtime(true);
    $image = new Image($imagePath);
    $palette = $image->getColors(Palette::PALETTE_COLOR_TOOLS, Color::COMPARE_GREAT, $precision);

    if($precision==Palette::ADAPTIVE_PRECISION) {
        $precision = 'adaptive precision';
    } else {
        $precision.= ' precision';
    }


    echo '<div style="border:1px solid black;box-shadow:0 0 10px rgba(0,0,0, .5);margin:15px;float:left;">
    <p style="margin:3px;text-indent:7px;">'.$imagePath.' with '.$precision.' - got '.count($palette).' colors in '.round(microtime(true) - $start, 3).'s</p>
    <img src="'.$imagePath.'" width="600" /><br/>
    <table style="border-collapse:collapse;width:600px;height:25px;font-size:12px;line-height:12px;margin:0;padding:0;">
        <tr>';
    foreach($palette as $hex=>$coverage) {
        if($coverage>3) {
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

showImageAndPalette('../samples/test.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test2.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test3.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test4.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test5.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test6.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test7.jpg', Palette::ADAPTIVE_PRECISION);


/*
showImageAndPalette('../samples/test.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test.jpg', 128);
showImageAndPalette('../samples/test.jpg', 64);
showImageAndPalette('../samples/test.jpg', 16);
echo '<hr>';
showImageAndPalette('../samples/test2.jpg', Palette::ADAPTIVE_PRECISION);
showImageAndPalette('../samples/test2.jpg', 128);
showImageAndPalette('../samples/test2.jpg', 64);
showImageAndPalette('../samples/test2.jpg', 16);
*/



?>


</body>
</html>