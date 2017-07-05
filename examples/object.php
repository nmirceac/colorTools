<?php
require_once('autoload.php');

use ColorTools\Image as Image;
use ColorTools\Store as Store;

Image::$settings = [
    'preferredEngine' => Image::ENGINE_IMAGICK,
    'resizing' => [
        'engine'=>Image::RESIZE_ENGINE_NATIVE,
        'imagick'=>[
            'adaptive'=>true,
            'filter'=>Image::RESIZE_FILTER_AUTO,
            'blur'=>0.25
        ],
        'gd'=>[
            'filter'=>Image::RESIZE_FILTER_AUTO
        ]
    ]
];

$image = Image::create('../samples/test.jpg');
$image->processModifiersString('-an=4-fi=26+5-ft=640+480');
//$image->fit(640, 480, Image::CROP_ANCHOR_BOTTOM);
//$image->applyFilter(Image::FILTER_OIL_PAINT, [5]);
print_r($image->getModifiersString());
//$image->rehash();
$store = new Store($image);
echo "<br>";
echo "<br>";
echo '<img src="'.$store->publish('jpeg').'"><br><hr><br>';

$image = Image::create(new Imagick('../samples/test5.jpg'));
$store = new Store($image);
echo "<br>";
echo "<br>";
$store->store();
exit();




$filter = Image::FILTER_OIL_PAINT;
//$params = [128, 55, 64];
//$params = [128, 55, 64, 64];
//$params = [127, 127, 127];
//$params = [5, 2, 45];
$params = [5];
//$params = [2];
//$params = [0.5];

$angle = -45;
$flip = Image::FLIP_BOTH;

$image = Image::create('../samples/test.jpg');
//print_r($image->serializeComplete());
$image->fit(640, 480, Image::CROP_ANCHOR_CENTER);
//$image->doRotate($angle);
//$image->doFlip($flip);
//$image->applyFilter($filter, $params);
echo '<img src="'.$image->getImageSrc().'"><br><hr><br>';
$image = Image::create(new Imagick('../samples/test5.jpg'));
$image->resizeCover(320, 240, Image::CROP_ANCHOR_CENTER);
$image->doRotate($angle);
//$image->doFlip($flip);
$image->applyFilter($filter, $params);
echo '<img src="'.$image->getImageSrc().'"><br><hr><br>';