<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qiniu extends MY_Controller {

    /**
     * 七牛上传token
     * 该方法供前端js上传调用，获取上传所需的token
     * @return json
     */
    public function index_get() {
        require APPPATH.'third_party/Qiniu-7.0.7/autoload.php';

        // parent::showProfiler(false);
        $this->output->enable_profiler(false);

        // qiniu账号
        $accessKey = config_item('qiniu.access_key');
        $secretKey = config_item('qiniu.secret_key');

        // 构建鉴权对象
        $auth = new Qiniu\Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = config_item('qiniu.bucket');

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        if(!empty($token)){
            $this->responseJson(200, '获取成功', ['uptoken'=>$token]);
        }else{
            $this->responseJson(502, "获取失败");
        }

    }

    /**
     * 图片从后端服务器上传到qiniu
     * @param $devpng 需上传的图片服务器地址
     */
    public function uploadToQiniu($devpng){

        require_once APPPATH.'third_party/Qiniu-7.0.7/autoload.php';

        $this->output->enable_profiler(false);

        // qiniu账号
        $accessKey = config_item('qiniu.access_key');
        $secretKey = config_item('qiniu.secret_key');

        // 构建鉴权对象
        $auth = new Qiniu\Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = config_item('qiniu.bucket');

        // 上传到七牛后保存的文件名
        $key = "upload/".date("Ymd")."/".$devpng.".png";

        // 生成上传 Token
        $policy = array(
            'scope'=>$bucket.":".$key,
            'insertOnly'=> 0,
        );
        $token = $auth->uploadToken($bucket, $key, 3600, $policy);

        // 要上传文件的本地路径
        $filePath = $devpng;

        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new Qiniu\Storage\UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
         echo "\n====> upload result: \n";
         if ($err !== null) {
             var_dump($err);
         } else {
             var_dump($ret);
         }
    }
}
