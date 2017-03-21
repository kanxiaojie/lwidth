<?php



$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/api', function () use ($app) {
    return response()->json(['name' => 'Abigail', 'state' => 'CA']);
});

$app->post('/encrypt/user','UserController@encryptCode');
$app->post('/decrypt/user','UserController@decryptCode');

$app->get('/weixin/check','WeixinController@check');
$app->get('/weixin/login','WeixinController@login');
$app->get('/weixin/userInfo','WeixinController@useInfo');

$app->post('/weixin/firstcode','WeixinController@firstLogin');

$app->get('/upload/image','UploadToQiniuController@index');
$app->post('/images/upload','UploadToQiniuController@uploadToQiniu');