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
          <td style="background-color: '.$similarColor->hex.';">'.$similarColor->hex.'</td>
          <td>'.$similarColor->name.'</td>
          <td style="background-color: '.$similarColor2->hex.';">'.$similarColor2->hex.'</td>
          <td>'.$similarColor2->name.'</td>
          <td style="background-color: '.$similarColor3->hex.';">'.$similarColor3->hex.'</td>
          <td>'.$similarColor3->name.'</td>
          </tr>';
}

?><html>
<body>
<table>
    <tr>
        <th>Random color</th><th>Found color</th><th>Color name</th>
        <th>Found color 2</th><th>Color 2 name</th>
        <th>Found color 3</th><th>Color 3 name</th>
    </tr>
    <?php
    showColorComparisonRow(Color::create('#64e36a')); //example where COMPARE_FAST kinda' fails
    showColorComparisonRow(Color::create('#610a41')); //example where COMPARE_GREAT gets it
    showColorComparisonRow(Color::create('#d846ad')); //example where COMPARE_GREAT and here
    showColorComparisonRow(Color::create('#ea469b')); //is this an example where COMPARE_GREAT is better - hotpink
    showColorComparisonRow(Color::create('#1282cb')); //each method got a different result
    showColorComparisonRow(Color::create('#55f087')); //is it just me or here COMPARE_FAST got the best match?
    showColorComparisonRow(Color::create('#77169a')); //also here - #rebeccapurple
    showColorComparisonRow(Color::create('#f1a388')); //do you eat salmon? light or dark?
    showColorComparisonRow(Color::create(0xffffff)); //spacer
    showColorComparisonRow(Color::create(0)); //spacer
    for($i=0;$i<50;$i++) {
        showColorComparisonRow();
    }
    ?>
</table>
</body>


</html>