<?php



$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/api', function () use ($app) {
    return response()->json(['name' => 'Abigail', 'state' => 'CA']);
});

$app->post('/encrypt/user','UserController@encryptCode');
$app->post('/decrypt/user','UserController@decryptCode');