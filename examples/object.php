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

$storeImages = [
    '006006c21314e4b29c74e7464579edc6',
    '1e7c11c0d40079227e4d7f8130b46035'
];

/*
$image = Image::create('../samples/test.jpg');
//$image->processModifiersString('-an=4-fi=26+5-ft=640+480');
$image->fit(640, 480, Image::CROP_ANCHOR_BOTTOM);
$image->doFlip(Image::FLIP_HORIZONTAL);
$image->applyFilter(Image::FILTER_OIL_PAINT, [5]);
//print_r($image->getModifiersString());
//$image->rehash();
//$store = new Store($image);
//$store = Store::findAndProcess('006006c21314e4b29c74e7464579edc6-an=4-fi=26+5-ft=640+480');
$store = Store::findByHash('006006c21314e4b29c74e7464579edc6')->processModifiersString('-an=4-fi=26+5-ft=640+480');
*/

foreach($storeImages as $image) {
    $store = Store::findByHash($image);
//    ->object->doRotate('90');
    $store->modifyImage(function(Image $img) {
        $img->setCropAnchor(Image::CROP_ANCHOR_LEFT);
        $img->fit(480, 480);
    });

    echo "<br>";
    echo "<br>";
    echo '<img src="'.$store->publish('jpeg').'"><br><hr><br>';
}
exit();

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