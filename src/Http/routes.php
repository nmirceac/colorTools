<?php

if(config('colortools.router.guestMiddleware')) {
    $router->get('/{image}', ['uses'=>'ImagesController@index', 'middleware' => config('colortools.router.guestMiddleware'), 'as'=>config('colortools.router.namedPrefix').'.get']);
} else {
    $router->get('/{image}', ['uses'=>'ImagesController@index', 'as'=>config('colortools.router.namedPrefix').'.get']);
}

$router->group(['middleware' => ['web', config('colortools.router.authMiddleware')]], function ($router) {
    $router->get('/h/{type}/{image}', ['uses'=>'ImagesController@histogram', 'as'=>config('colortools.router.namedPrefix').'.histogram']);
    $router->get('/preview/{image}', ['uses'=>'ImagesController@preview', 'as'=>config('colortools.router.namedPrefix').'.preview']);
    $router->get('/download/{image}', ['uses'=>'ImagesController@download', 'as'=>config('colortools.router.namedPrefix').'.download']);
    $router->post('/upload', ['uses'=>'ImagesController@upload', 'as'=>config('colortools.router.namedPrefix').'.upload']);
    $router->post('/attach', ['uses'=>'ImagesController@attach', 'as'=>config('colortools.router.namedPrefix').'.attach']);
    $router->post('/delete', ['uses'=>'ImagesController@delete', 'as'=>config('colortools.router.namedPrefix').'.delete']);
    $router->post('/replace', ['uses'=>'ImagesController@replace', 'as'=>config('colortools.router.namedPrefix').'.replace']);
    $router->post('/update', ['uses'=>'ImagesController@update', 'as'=>config('colortools.router.namedPrefix').'.update']);
    $router->post('/updateMetadata', ['uses'=>'ImagesController@updateMetadata', 'as'=>config('colortools.router.namedPrefix').'.updateMetadata']);
    $router->post('/updateRelatedModelDetails', ['uses'=>'ImagesController@updateRelatedModelDetails', 'as'=>config('colortools.router.namedPrefix').'.updateRelatedModelDetails']);
    $router->post('/reorder', ['uses'=>'ImagesController@reorder', 'as'=>config('colortools.router.namedPrefix').'.reorder']);
    $router->post('/associations', ['uses'=>'ImagesController@associations', 'as'=>config('colortools.router.namedPrefix').'.associations']);
    $router->post('/associatedModels', ['uses'=>'ImagesController@associatedModels', 'as'=>config('colortools.router.namedPrefix').'.associatedModels']);
    $router->post('/associatedModelsPaginated', ['uses'=>'ImagesController@associatedModelsPaginated', 'as'=>config('colortools.router.namedPrefix').'.associatedModelsPaginated']);
});
