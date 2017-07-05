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

$requestedImage = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
$requestedImage = explode('.', $requestedImage);
$imageInfo = $requestedImage[0];
$type = $requestedImage[1];

$path = Store::findAndProcess($imageInfo)->publish($type);
header('Location: ../'.$path);
exit();