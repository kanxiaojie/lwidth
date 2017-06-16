<?php namespace App\Repositories;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

class QiniuRepository
{
    public function deleteImageFormQiniu($key) {
        $accessKey= "WR4zSNb376JZyEq6TQx9pJ9DSherW9xFKO9Ls2zB";
        $secretKey= "z7_Jx8-sCMGUFmrP5bPqM_GOT2FiIq3AEeEoQKZE";
        $auth=new Auth($accessKey, $secretKey);
        $bucketMgr = new BucketManager($auth);
        $bucket= "lovewall";//上传空间名称

        $err = $bucketMgr->delete($bucket, $key);
        echo "\n====> delete $key : \n";
        if ($err !== null) {
            $result = $err;
        } else {
            $result = "Success!"
        }

        return $result;
    }
}