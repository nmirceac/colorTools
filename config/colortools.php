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
        'preferredEngine' => \ColorTools\Image::ENGINE_IMAGICK,
        'resizing' => [
            'engine'=>\ColorTools\Image::RESIZE_ENGINE_NATIVE,
            'imagick'=>[
                'adaptive'=>true,
                'filter'=>\ColorTools\Image::RESIZE_FILTER_AUTO,
                'blur'=>0.25
            ],
            'gd'=>[
                'filter'=>\ColorTools\Image::RESIZE_FILTER_AUTO
            ]
        ]
    ]
];

