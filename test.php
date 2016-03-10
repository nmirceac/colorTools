<html><?php require_once('Color.php'); ?>
<body>
<table>
    <tr>
        <th>Random color</th><th>Found color</th><th>Color name</th><th>Found color 2</th><th>Color 2 name</th><th>Found color 3</th><th>Color 3 name</th>
    </tr>
    <?php
    for($i=0;$i<20;$i++) {
        $color=Color::create(rand(0, 0xffffff));
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
                  <td>'.$similarColor3->name.'</td></tr>';
    }
    ?>
</table>
</body>


</html>