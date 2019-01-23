<?php

return [
    'router'=> [
        'includeRoutes'=>true,
        'returnRelativeUrls'=>true,
        'prefix'=>'images',
        'namedPrefix'=>'image',
        'guestMiddleware'=>'web',
        'authMiddleware'=>'auth'
    ],

    'store'=> [
        'storeBasePath'=>'../store/',
        'publicPattern'=>'images/%hash%',
        'publicPath'=>'images',
        'optimizeAfterPublish' => [
            'jpeg'=>true,
            'png'=>true
        ],
        'optimizeCommand' => [
            'jpegoptimParams'=> '-s --all-progressive -m95',
            'optipngParams'=> '-o2'
        ]
    ],

    'image' => [
        'preferredEngine' => \ColorTools\Image::ENGINE_GD,
        'resizing' => [
            'engine'=>\ColorTools\Image::RESIZE_ENGINE_NATIVE,
            'imagick'=>[
                'adaptive'=>false,
                'filter'=>\Imagick::FILTER_SINC,
                'blur'=>0.75
            ],
            'gd'=>[
                'filter'=>IMG_SINC
            ]
        ]
    ]
];

