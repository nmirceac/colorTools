<?php
require_once('autoload.php');

use ColorTools\Image as Image;


if(isset($_GET['image'])) {
    switch($_GET['image']) {
        case 0 :
            exit(Image::create('../samples/test.jpg')->displayImage());
            break;

        case 1 :
            exit(Image::create(file_get_contents('../samples/test2.jpg'))->displayImage());
            break;

        case 2 :
            exit(Image::create(imagecreatefromjpeg('../samples/test3.jpg'))->displayImage());
            break;

        case 3 :
            $testUrl ='http://'.$_SERVER['SERVER_NAME'];
            $testUrl.=substr($_SERVER['REQUEST_URI'],0, strrpos($_SERVER['REQUEST_URI'], '/')+1).'../samples/test4.jpg';
            exit(Image::create($testUrl)->displayImage());
            break;

        case 4 :
            exit(Image::create(new Imagick('../samples/test5.jpg'))->displayImage());
            break;

        case 5 :
            exit(Image::create(new Imagick('../samples/test6.jpg'))->displayImage('png'));
            break;

        case 6 :
            exit(Image::create(imagecreatefromjpeg('../samples/test7.jpg'))->displayImage('gif'));
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