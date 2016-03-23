<?php require_once('../vendor/autoload.php');

if(!file_exists('../vendor/autoload.php')) {
    throw new \Exception('Try running composer update first in '.realpath('..'));
} else {
    require_once('../vendor/autoload.php');
}

use ColorTools\Color as Color;

function showColorComparisonRow($color = null) {
    if(is_null($color)) {
        $color=Color::create(rand(0, 0xffffff));
    }

    $similarColor = $color->findSimilar(Color::COMPARE_FAST);
    $similarColor2 = $color->findSimilar(Color::COMPARE_NORMAL);
    $similarColor3 = $color->findSimilar(Color::COMPARE_GREAT);

    if($similarColor!=$similarColor2 or $similarColor!=$similarColor3 or $similarColor2!=$similarColor3) {
        $trStyle='background-color:gray;';
    } else {
        $trStyle='';
    }
    echo '<tr style="'.$trStyle.'">
          <td style="background-color: '.$color->hex.';">'.$color->hex.'</td>
          <td style="background-color: '.$color->hsl.';">'.$color->hsl.'</td>
          <td style="background-color: '.$color->rgb.';">'.$color->rgb.'</td>
          <td style="background-color: '.$similarColor->hex.';">'.$similarColor->hex.'</td>
          <td>'.$similarColor->name.'</td>
          <td style="background-color: '.$similarColor2->hex.';">'.$similarColor2->hex.'</td>
          <td>'.$similarColor2->name.'</td>
          <td style="background-color: '.$similarColor3->hex.';">'.$similarColor3->hex.'</td>
          <td>'.$similarColor3->name.'</td>
          </tr>';
}

function showColorFunctions($color = null) {
    if(is_null($color)) {
        $color=Color::create(rand(0, 0xffffff));
    }

    $gray = Color::create($color)->grayscale();
    $spin = Color::create($color)->spin(60);
    $invert = Color::create($color)->invert();
    $complement = Color::create($color)->complement();
    $saturated = Color::create($color)->saturate(20);
    $desaturated = Color::create($color)->desaturate(20);
    $lightened = Color::create($color)->lighten(10);
    $darkened = Color::create($color)->darken(10);

    echo '<tr>
          <td style="background-color: '.$color->hex.';">'.$color->hex.'</td>
          <td style="background-color: '.$gray->hex.';">'.$gray->hex.'</td>
          <td style="background-color: '.$spin->hex.';">'.$spin->hex.'</td>
          <td style="background-color: '.$invert->hex.';">'.$invert->hex.'</td>
          <td style="background-color: '.$complement->hex.';">'.$complement->hex.'</td>
          <td style="background-color: '.$saturated->hex.';">'.$saturated->hex.'</td>
          <td style="background-color: '.$desaturated->hex.';">'.$desaturated->hex.'</td>
          <td style="background-color: '.$lightened->hex.';">'.$lightened->hex.'</td>
          <td style="background-color: '.$darkened->hex.';">'.$darkened->hex.'</td>


          </tr>';
}

function showColorMixing($color = null) {
    if(is_null($color)) {
        $color=Color::create(rand(0, 0xffffff));
    }

    $red = Color::create($color)->mix(Color::create('red'), 0.3);
    $blue = Color::create($color)->mix(Color::create('lime'), 0.3);
    $green = Color::create($color)->mix(Color::create('blue'), 0.3);

    $tint = Color::create($color)->tint(0.1);
    $tint2 = Color::create($color)->tint(0.3);
    $shade = Color::create($color)->shade(0.1);
    $shade2 = Color::create($color)->shade(0.3);

    echo '<tr>
          <td style="background-color: '.$color->hex.';">'.$color->hex.'</td>
          <td style="background-color: '.$color->hsl.';">'.$color->hsl.'</td>
          <td style="background-color: '.$red->hex.';">'.$red->hex.'</td>
          <td style="background-color: '.$blue->hex.';">'.$blue->hex.'</td>
          <td style="background-color: '.$green->hex.';">'.$green->hex.'</td>
          <td style="background-color: '.$tint->hex.';">'.$tint->hex.'</td>
          <td style="background-color: '.$tint2->hex.';">'.$tint2->hex.'</td>
          <td style="background-color: '.$shade->hex.';">'.$shade->hex.'</td>
          <td style="background-color: '.$shade2->hex.';">'.$shade2->hex.'</td>
          </tr>';
}

function showColorBlending($color = null) {
    if(is_null($color)) {
        $color=Color::create(rand(0, 0xffffff));
    }

    $multiply = Color::create($color)->multiply('#888');
    $screen = Color::create($color)->screen('#888');
    $overlayRed = Color::create($color)->overlay(Color::create('red'));
    $overlayGreen = Color::create($color)->overlay(Color::create('lime'));
    $overlayBlue = Color::create($color)->overlay(Color::create('blue'));
    $softlight = Color::create($color)->softlight('#888');
    $hardlight = Color::create($color)->hardlight('#888');


    echo '<tr>
          <td style="background-color: '.$color->hex.';">'.$color->hex.'</td>
          <td style="background-color: '.$color->hsl.';">'.$color->hsl.'</td>
          <td style="background-color: '.$multiply->hex.';">'.$multiply->hex.'</td>
          <td style="background-color: '.$screen->hex.';">'.$screen->hex.'</td>
          <td style="background-color: '.$overlayRed->hex.';">'.$overlayRed->hex.'</td>
          <td style="background-color: '.$overlayGreen->hex.';">'.$overlayGreen->hex.'</td>
          <td style="background-color: '.$overlayBlue->hex.';">'.$overlayBlue->hex.'</td>
          <td style="background-color: '.$softlight->hex.';">'.$softlight->hex.'</td>
          <td style="background-color: '.$hardlight->hex.';">'.$hardlight->hex.'</td>
          </tr>';
}

function showColorBlending2($color = null) {
    if(is_null($color)) {
        $color=Color::create(rand(0, 0xffffff));
    }

    $difference = Color::create($color)->difference('#888');
    $exclusion = Color::create($color)->exclusion('#888');
    $average = Color::create($color)->average('#888');
    $negate = Color::create($color)->negate('#888');
    $contrast = Color::create($color)->findConstrast();


    $overlayRed = Color::create($color)->overlay(Color::create('red'));
    $overlayGreen = Color::create($color)->overlay(Color::create('lime'));
    $overlayBlue = Color::create($color)->overlay(Color::create('blue'));
    $softlight = Color::create($color)->softlight('#888');
    $hardlight = Color::create($color)->hardlight('#888');


    echo '<tr>
          <td style="background-color: '.$color->hex.';">'.$color->hex.'</td>
          <td style="background-color: '.$color->hsl.';">'.$color->hsl.'</td>
          <td style="background-color: '.$difference->hex.';">'.$difference->hex.'</td>
          <td style="background-color: '.$exclusion->hex.';">'.$exclusion->hex.'</td>
          <td style="background-color: '.$average->hex.';">'.$average->hex.'</td>
          <td style="background-color: '.$negate->hex.';">'.$negate->hex.'</td>
          <td style="background-color: '.'#fff'.';">'.$color->luma.'</td>
          <td style="background-color: '.$color->hex.'; color: '.$contrast->hex.';">'.$contrast->hex.'</td>
          <td style="background-color: '.$hardlight->hex.';">'.$hardlight->hex.'</td>
          </tr>';
}





?><html>
<body>
<table>
    <tr>
        <th>Random color</th><th>HSL</th><th>RGB</th>
        <th>Found color</th><th>Color name</th>
        <th>Found color 2</th><th>Color 2 name</th>
        <th>Found color 3</th>
    </tr>
    <?php
    showColorComparisonRow(Color::create(0xffffff));
    showColorComparisonRow(Color::create('#64e36a')); //example where COMPARE_FAST kinda' fails
    showColorComparisonRow(Color::create('#610a41')); //example where COMPARE_GREAT gets it
    showColorComparisonRow(Color::create('#d846ad')); //example where COMPARE_GREAT and here
    showColorComparisonRow(Color::create('#ea469b')); //is this an example where COMPARE_GREAT is better - hotpink
    showColorComparisonRow(Color::create('#1282cb')); //each method got a different result
    showColorComparisonRow(Color::create('#55f087')); //is it just me or here COMPARE_FAST got the best match?
    showColorComparisonRow(Color::create('#77169a')); //also here - #rebeccapurple
    showColorComparisonRow(Color::create('#f1a388')); //do you eat salmon? light or dark?
    for($i=0;$i<10;$i++) {
        showColorComparisonRow();
    }
    showColorComparisonRow(Color::create(0));
    ?>
    <tr><td colspan="8">&nbsp;</td></tr>
    <tr>
        <th>Random color</th><th>Grayscale</th><th>Spin 60</th>
        <th>Invert</th><th>Complement</th>
        <th>Saturation +20%</th><th>Desaturation -20%</th>
        <th>Lighten +10%</th><th>Darken -10%</th>
    </tr>
    <?php
    showColorFunctions(Color::create(0xffffff));
    for($i=0;$i<10;$i++) {
        showColorFunctions();
    }
    showColorFunctions(Color::create(0));
    ?>
    <tr><td colspan="8">&nbsp;</td></tr>
    <tr>
        <th>Random color</th><th>HSL</th>
        <th>Red mix 30%</th><th>Green mix 30%</th><th>Blue mix 30%</th>
        <th>Tint 10%</th><th>Tint 30%</th>
        <th>Shade 10%</th><th>Shade 30%</th>
    </tr>
    <?php
    showColorMixing(Color::create(0xffffff));
    for($i=0;$i<10;$i++) {
        showColorMixing();
    }
    showColorMixing(Color::create(0));
    ?>
    <tr><td colspan="8">&nbsp;</td></tr>
    <tr>
        <th>Random color</th><th>HSL</th>
        <th>Multiply (#888)</th><th>Screen (#888)</th>
        <th>Overlay red</th><th>Overlay green</th><th>Overlay blue</th>
        <th>Softlight</th><th>Hardlight</th>
    </tr>
    <?php
    showColorBlending(Color::create(0xffffff));
    showColorBlending(Color::create('#ff6600'));
    for($i=0;$i<10;$i++) {
        showColorBlending();
    }
    showColorBlending2(Color::create(0));
    ?>
    <tr><td colspan="8">&nbsp;</td></tr>
    <tr>
        <th>Random color</th><th>HSL</th>
        <th>Difference (#888)</th><th>Exclusion (#888)</th>
        <th>Average (#888)</th><th>Negate(#888)</th><th>Luma</th>
        <th>Contrast</th><th>Hardlight</th>
    </tr>
    <?php
    showColorBlending2(Color::create(0xffffff));
    showColorBlending2(Color::create('#ff6600'));
    for($i=0;$i<10;$i++) {
        showColorBlending2();
    }
    showColorBlending2(Color::create(0));
    ?>
</table>
</body>


</html>