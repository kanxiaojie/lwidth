<?php



$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/api', function () use ($app) {
    return response()->json(['name' => 'Abigail', 'state' => 'CA']);
});


$app->post('/api/encrypt/user','UserController@encryptCode');
$app->post('/api/decrypt/user','UserController@decryptCode');

$app->get('/api/weixin/check','WeixinController@check');
$app->get('/api/weixin/login','WeixinController@login');
$app->get('/api/weixin/userInfo','WeixinController@useInfo');

$app->post('/api/weixin/firstcode','WeixinController@firstLogin');

$app->get('/api/upload/image','UploadToQiniuController@index');

$app->post('/api/images/upload','UploadToQiniuController@uploadToQiniu');

//帖子
$app->get('/api/posts/lists','PostController@index');