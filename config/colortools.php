<?php

return [
    'router'=> [
        'includeRoutes'=>true,
        'returnRelativeUrls'=>true,
        'prefix'=>'images',
        'namedPrefix'=>'image',
        'guestMiddleware'=>'',
        'authMiddleware'=>'auth'
    ],

    'key'=>'',
    'refererBypassSignatureCheck'=>[
        // 'subdomain.example.tld',
    ],

    'store'=> [
        'storeBasePath'=>'../store/',
        'publicPattern'=>'images/%hash%',
        'publicPath'=>'images',
        'analyzeAfterCreate' => true,
        'optimizeAfterPublish' => [
            'jpeg'=>true,
            'png'=>true
        ],
        'optimizeCommand' => [
            'jpegoptimParams'=> '-s --all-progressive -m90',
            'optipngParams'=> '-strip all -o2'
        ],
        'jpegoptimBinaryPath' => 'auto',
        'optipngBinaryPath' => 'auto',
    ],

    'rekognition'=> [
        'key'=>env('AWS_KEY'),
        'secret'=>env('AWS_SECRET'),
        'region'=>env('AWS_REGION'),
    ],

    'image' => [
        'quality'=>90,
        'preferredEngine' => \ColorTools\Image::ENGINE_GD,
        'resizing' => [
            'engine'=>\ColorTools\Image::RESIZE_ENGINE_NATIVE,
            'imagick'=>[
                'adaptive'=>false,
                'filter'=>14, // \Imagick::FILTER_SINC,
                'blur'=>0.75
            ],
            'gd'=>[
                'filter'=>19 // IMG_SINC
            ]
        ]
    ]
];

