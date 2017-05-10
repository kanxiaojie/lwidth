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




$app->get('/api/upload/image','UploadToQiniuController@index');

//发表帖子和上传图片同时进行
$app->post('/api/images/upload','UploadToQiniuController@uploadToQiniu');

//注册
$app->post('/api/register','WeixinController@firstLogin');

//发表白接口
$app->post('/api/loves','PostController@publishPost');
$app->post('/api/loves/images','PostController@uploadPostImages');
//虚拟用户发布帖子
$app->post('/api/virtual/loves','PostController@virtualPublishPost');
$app->post('/api/virtual/loves/images','PostController@virtualUploadPostImages');

//所有帖子
$app->get('/api/posts','PostController@index');

//所有帖子
$app->get('/api/loves','PostController@lists');

//获取本校表白  (本校的表白 时间倒序)
$app->get('/api/collegeLoves','PostController@getCollegeLoves');

//获取我发表的所有表白
$app->get('/api/myLoves','PostController@getMyLoves');

//获取单个表白及评论内容
$app->get('/api/loves/{id}/comments','PostController@postAndSelfComments');

//给某条表白评论
$app->post('/api/loves/{id}/comments','CommentController@publishComments');

//给某条表白点赞/或取消点赞
$app->post('/api/loves/{id}/praises','PraiseController@praiseToPost');

//给某条评论再评论 改为回复
$app->post('/api/comments/{id}/replies','CommentController@commentToComment');

//获取某条评论的所有再评论
$app->get('/api/comments/{id}/comments','CommentController@getCommentToComments');

//获取照片墙
$app->get('/api/pictures','UserController@getPictures');

//获取最热门的表白（排列顺序是  评论数*2 + 点赞数）
$app->get('/api/hotLoves','PostController@getHotPosts');

//通过地图查看附近的表白
$app->get('/api/locationLoves','PostController@getLocationLoves');

//提交/或者更新 个人详细信息
$app->post('/api/users','UserController@updateUser');
$app->post('/api/users/pictures','UserController@uploadUserImage');
//获取某人的详细信息
$app->get('/api/users/{id}','UserController@getUserInfo');
$app->get('/api/user','UserController@getUserInfoByOpenId');

//删除个人照片
$app->post('/api/delete/user/picture','UserController@deletePicture');

//删除表白
$app->post('/api/delete/love','PostController@deletePost');

$app->get('/api/colleges','ExampleController@getColleges');
$app->get('/api/grades','ExampleController@getGrades');


