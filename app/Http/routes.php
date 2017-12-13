<?php

$app->get('/', function () use ($app) {
    return $app->version();
});

//以下3个api做测试用
$app->post('/api/encrypt/user','UserController@encryptCode');
$app->post('/api/decrypt/user','UserController@decryptCode');
$app->get('/api/weixin/userInfo','WeixinController@useInfo');

//发表帖子和上传图片同时进行--切换至前端上传
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
// $app->get('/api/loves','PostController@lists');
$app->get('/api/loves','PostController@getLoves');

//获取本校表白  (本校的表白 时间倒序)
$app->get('/api/collegeLoves','PostController@getCollegeLoves');

//获取我发表的所有表白
$app->get('/api/myLoves','PostController@getMyLoves');

//给某条表白评论
$app->post('/api/loves/{id}/comments','CommentController@publishComments');

//给某条表白点赞/或取消点赞
$app->post('/api/loves/{id}/praises','PraiseController@praiseToPost');

//给某条评论再评论 改为回复
$app->post('/api/comments/{id}/replies','CommentController@commentToComment');

//给某条回复点赞/或取消点赞
$app->post('/api/replies/{id}/praises','PraiseController@praiseToReplies');

//给某条评论点赞/或取消点赞
$app->post('/api/comments/{id}/praises','PraiseController@praiseToComment');

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

//删除某条评论
$app->post('/api/delete/comment','CommentController@deleteComment');
//删除某条回复
$app->post('/api/delete/reply','CommentController@deleteReply');

//获取所有的举报类型
$app->get('/api/badReportTypes','BadReportTypeController@getBadReportTypes');

//举报某条帖子
$app->post('api/badReports/love/{id}','BadReportTypeController@reportPost');
$app->post('api/badReports/comment/{id}','BadReportTypeController@reportComment');
$app->post('api/badReports/reply/{id}','BadReportTypeController@reportReply');
$app->post('api/badReports/user/{id}','BadReportTypeController@reportUser');
$app->post('api/badReports/{typeId}/{id}','BadReportTypeController@report');

//获取我评论过的帖子/我赞过的帖子  (新增需求提示  只要是获取表白列表的  都需要支持 page search  wesecret参数)
$app->get('api/myCommentLoves','CommentController@getMyCommentPosts');
$app->get('api/myPraiseLoves','CommentController@getMyPraisePosts');

//从后端获取上传图片到七牛云所需要的 uptoken
$app->get('/api/uptoken','UploadToQiniuController@getUpToken');

//获取我未读的帖子数
$app->get('/api/unreadLoveNums','PostController@getUnreadLoveNums');

//对用户点赞/取消点赞
$app->post('/api/users/{id}/praises','PraiseController@praiseToUser');

//22-1-1   获取单个表白
$app->get('/api/loves/{id}','PostController@getLove');

//获取点赞我的人
$app->get('/api/praiseMeUsers','PraiseController@getPraiseMeUsers');

//获取某个表白的所有评论
$app->get('/api/loves/{id}/comments','PostController@getPostAllComments');

//获取单条评论
$app->get('/api/comments/{id}','CommentController@getCommentInfo');
//获取某条评论的所有回复
$app->get('/api/comments/{id}/replies','CommentController@getCommentReplyInfos');

//19  获取我未读的提醒数
$app->get('/api/unreadNoticeNums','NoticeController@getUnreadNoticeNums');

//获取我未读的系统通知数
$app->get('/api/unreadSystemNoticeNums','SystemNoticeController@getUnreadSystemNoticeNums');

//获取所有的提醒
$app->get('/api/notices','NoticeController@getNotices');

//获取所有的系统通知
$app->get('/api/systemNotices','SystemNoticeController@getSystemNotices');

//标注评论/回复已读
$app->post('/api/read/notice','NoticeController@labelRead');

//标注系统通知已读
$app->post('/api/read/systemNotice','SystemNoticeController@labelRead');

//获取异性表白
$app->get('/api/genderLoves','PostController@getGenderLoves');

//查看我的黑名单中的用户
$app->get('/api/blacklists','UserController@getBlackLists');
//把某人加入黑名单或者从黑名单中移除
$app->post('/api/blacklists','UserController@addOrRemoveBlackLists');

//获取相关小程序
$app->get('/api/applets','SystemNoticeController@get_applets');

//获取关于表白墙
$app->get('/api/aboutLoveWalls','SystemNoticeController@get_aboutLoveWalls');
//获取关于校园服务号
$app->get('/api/aboutCollegeServices','SystemNoticeController@get_aboutCollegeServices');


//获取/省份/城市/学校/关注范围
$app->get('/api/provinces', 'ExampleController@getProvinces');
$app->get('/api/provinces/{id}/cities', 'ExampleController@getCities');
$app->get('/api/cities/{id}/colleges','ExampleController@getColleges');
$app->get('/api/interests', 'ExampleController@getInterests');
$app->get('/api/postingTypes', 'ExampleController@getPostingTypes');

// 审核是否可见
$app->get('/api/get_available','SystemNoticeController@get_available');
$app->get('/api/get_availables','SystemNoticeController@get_availables');





//电台
$app->get('/api/radios','ApiController@getRadios');
$app->get('/api/radios/{id}','ApiController@getRadio');




//后台管理系统API--------------------------------------------------------------------------------------------------------------


//登录接口
$app->post('/admin/login','AdminUserController@login');


//获取所有的系统通知
$app->get('/api/systemNotices_backsystem','SystemNoticeController@getSystemNotices_backsystem');
//发布系统通知
$app->post('/api/systemNotices','SystemNoticeController@postSystemNotices');
//删除系统通知
$app->post('/api/delete/systemNotice','SystemNoticeController@deleteSystemNotice');
//获取所有的相关小程序
$app->get('/api/relatedApplets','SystemNoticeController@getRelatedApplets');
//发布相关小程序
$app->post('/api/relatedApplets','SystemNoticeController@postRelatedApplets');
//删除相关小程序
$app->post('/api/delete/relatedApplet','SystemNoticeController@deleteRelatedApplet');
//获取所有的关于表白墙
$app->get('/api/aboutLoveWalls','SystemNoticeController@getAboutLoveWalls');
//发布关于表白墙
$app->post('/api/aboutLoveWalls','SystemNoticeController@postAboutLoveWalls');
//删除关于表白墙
$app->post('/api/delete/aboutLoveWall','SystemNoticeController@deleteAboutLoveWall');

//获取用户的详细信息
$app->get('/api/users','UserController@getUsers');
//编辑用户的详细信息
$app->post('/api/users','UserController@editUser');




//校园生活墙订阅号
//验证微信服务器
$app->get('/api/subscribe/init', 'WeixinController@subscribe_init');
$app->get('/api/getip', 'PostController@getip');


//私信
$app->post('/api/save_private_message','ApiController@savePrivateMessage');
$app->get('/api/get_private_message','ApiController@getPrivateMessage');

//电台
$app->get('/api/get_radio/list','ApiController@getRadioList');
$app->post('/api/radios','ApiController@postRadio');
$app->post('/api/delete/radio','ApiController@deleteRadio');

