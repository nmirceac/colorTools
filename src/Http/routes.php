<?php

$router->get('/{image}', ['uses'=>'ImagesController@index', 'as'=>config('colortools.router.namedPrefix').'.get']);

$router->group(['middleware' => config('colortools.router.authMiddleware')], function ($router) {
    $router->get('/h/{type}/{image}', ['uses'=>'ImagesController@histogram', 'as'=>config('colortools.router.namedPrefix').'.histogram']);
    $router->get('/preview/{image}', ['uses'=>'ImagesController@preview', 'as'=>config('colortools.router.namedPrefix').'.preview']);
    $router->get('/download/{image}', ['uses'=>'ImagesController@image', 'as'=>config('colortools.router.namedPrefix').'.image']);
    $router->post('/upload', ['uses'=>'ImagesController@upload', 'as'=>config('colortools.router.namedPrefix').'.upload']);
    $router->post('/attach', ['uses'=>'ImagesController@attach', 'as'=>config('colortools.router.namedPrefix').'.attach']);
    $router->post('/delete', ['uses'=>'ImagesController@delete', 'as'=>config('colortools.router.namedPrefix').'.delete']);
    $router->post('/replace', ['uses'=>'ImagesController@replace', 'as'=>config('colortools.router.namedPrefix').'.replace']);
    $router->post('/update', ['uses'=>'ImagesController@update', 'as'=>config('colortools.router.namedPrefix').'.update']);
    $router->post('/reorder', ['uses'=>'ImagesController@reorder', 'as'=>config('colortools.router.namedPrefix').'.reorder']);
});
