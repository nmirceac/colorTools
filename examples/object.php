<?php
require_once('autoload.php');

use ColorTools\Image as Image;

//$image = Image::create('../samples/test.jpg');
//$image->convertObjectTypeToImagick();

$image = Image::create(new Imagick('../samples/test5.jpg'));
$image->fit(640, 640, Image::CROP_ANCHOR_CENTER);
echo '<img src="'.$image->getImageSrc().'">';

