<?php
require_once('autoloader.php');

use ColorTools\Image as Image;


if(isset($_GET['image'])) {
    switch($_GET['image']) {
        case 0 :
            Image::create('../samples/test.jpg')->displayImage();
            break;

        case 1 :
            Image::create(file_get_contents('../samples/test2.jpg'))->displayImage();
            break;

        case 2 :
            Image::create(imagecreatefromjpeg('../samples/test3.jpg'))->displayImage();
            break;

        case 3 :
            $testUrl ='http://'.$_SERVER['SERVER_NAME'];
            $testUrl.=substr($_SERVER['REQUEST_URI'],0, strrpos($_SERVER['REQUEST_URI'], '/')+1).'../samples/test4.jpg';
            Image::create($testUrl)->displayImage();
            break;

        case 4 :
            Image::create(new Imagick('../samples/test5.jpg'))->displayImage();
            break;

        case 5 :
            Image::create(new Imagick('../samples/test6.jpg'))->displayImage('png');
            break;

        case 6 :
            Image::create(imagecreatefromjpeg('../samples/test7.jpg'))->displayImage('gif');
            break;

        default :
            break;
    }
    exit();
}

?><html>
<body>
<?php
for($i=0; $i<7; $i++) {
    echo '<div style="border:1px solid black;box-shadow:0 0 10px rgba(0,0,0, .5);margin:15px;float:left;">';
    echo '<img src="display.php?image='.$i.'" width="600" /><br/>';
    echo '</div>';
}
?>
</body>
</html>