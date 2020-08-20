<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->helper('cookie');
        $this->load->helper('used_helper');
    }

    //微信用户进行公众号授权
    public function oauth()
    {
        $notice = $this->wechat->notice;
        $templateId = 'tic8-fQPEnGzK38IlyQBNsVbcesdEOtp1a_m7UUN3Kc';
        $url = 'http://weixin.eachfight.com/#/mantio/';  //大神端入口地址
        $data = array(
            "first" => "收到一笔新的陪练需求",
            "keyword1" => time(),
        );

        $weixin_user = $this->wechat->user->batchGet(['oDfTV1C71uJfWGaI5vcMWrktCg3c'])->toArray();
        foreach ($weixin_user['user_info_list'] as $val) {
            if ($val['subscribe'] == 0) continue;
            $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($val['openid'])->send();
            log_message('info', '给大神推送模板消息opendid:' . $val['openid'] . '--' . json_encode($result));
        }
        dump($weixin_user);exit;

        $callback = urldecode($this->input->get('url')) . '?code=200';
        if (!$this->session->has_userdata($this->wechat_key)) {
            $response = $this->wechat->oauth->with(['state' => urlencode($callback)])->redirect();
            $response->send();
        } else {
            redirect($callback);
        }
    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack()
    {
        $user = $this->wechat->user->get('o05NB0w96SrxDgpS6ZzOapUNq1WY');
        dump($user);
        exit;

        $userArr = $user->toArray();
        $this->session->set_userdata([$this->wechat_key => $userArr['id']]);
        set_cookie('token', $userArr['id'], time() + 7200, '.eachfight.com', '/');
        redirect(urldecode($this->input->get('state')));
    }

    /**
     * 获取wx.config
     */
    public function getWxConfig()
    {
        $url = $this->input->get('url');
        $url = urldecode($url);
        if(!$url) $this->responseToJson(502, 'url参数缺少');
        log_message('info', 'getWxConfig获取到的url:' . $url);

        try {
            $jssdk = $this->wechat->js;
            $jssdk->setUrl($url);
            $data = $jssdk->config(
                ['onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone','chooseWXPay','chooseImage',
                 'previewImage','uploadImage','downloadImage','getLocalImgData','getNetworkType'],
                true, false, false);
            $this->responseToJson(200, '获取成功', $data);
        } catch (\Exception $exception) {
            $this->responseToJson(502, $exception->getMessage());
        }
    }


    /**
     * 前端给code 授权获取用户访问随机token  注册入库
     */
    public function weboauth()
    {
        $User_Model = new User_Model();
        $code = $this->input->get('code', true);

        if (empty($code)) $this->responseToJson(502, 'code参数缺少');
        log_message('info', 'weboauth获取到的code:' . $code);

        try {
            $user = $this->wechat->oauth->user();
            $data = $user->getOriginal();
            //随机token
            $token = uuid();
            //给token赋值并加密 设置有效期7200s
            $this->cache->redis->save($token, md5($data['openid'] . 'eachfight'), 7200);
            log_message('info', '授权获取用户随机token:' . $token);

            //没有注册过 注册
            if (!$this->User_Model->CheckRegister($data['openid'])) {
                if (!$User_Model->insert([
                    'openid' => $data['openid'],
                    'token' => $token,
                    'nickname' => replace_emoji($data['nickname']),
                    'gender' => $data['sex'],  //1时是男性，值为2时是女性，值为0时是未知
                    'headimg_url' => $data['headimgurl'],
                    'create_time' => date('Y-m-d H:i:s')
                ])) {
                    throw new \Exception('用户注册入库失败');
                }
            } else {  //更新token
                if (!$User_Model->update(['openid' => $data['openid']],
                    ['token' => $token, 'update_time' => date('Y-m-d H:i:s')])) {
                    throw new \Exception('更新访问token失败');
                }
            }

            $this->responseToJson(200, '登陆成功', ['token' => $token]);
        } catch (\Exception $e) {
            $this->responseToJson(502, $e->getMessage());
        }
    }
}
