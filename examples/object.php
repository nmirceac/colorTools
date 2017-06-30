<?php
require_once('autoload.php');

use ColorTools\Image as Image;

$filter = 42;
//$params = [128, 55, 64];
//$params = [128, 55, 64, 64];
//$params = [127, 127, 127];
//$params = [5, 2, 45];
$params = [0.5, 1];
//$params = [55];
//$params = [2];
//$params = [0.5];

$image = Image::create('../samples/test.jpg');
$image->resizeCover(320, 240, Image::CROP_ANCHOR_CENTER);
$image->applyFilter($filter, $params);
echo '<img src="'.$image->getImageSrc().'"><br><hr><br>';
$image = Image::create(new Imagick('../samples/test5.jpg'));
$image->resizeCover(320, 240, Image::CROP_ANCHOR_CENTER);
$image->applyFilter($filter, $params);
echo '<img src="'.$image->getImageSrc().'"><br><hr><br>';