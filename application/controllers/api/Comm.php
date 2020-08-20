<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Comm extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Sms');
        $this->load->helper('used_helper');

        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->model('Order_Model');
        $this->load->model('UserCashJournal_Model');
        $this->load->model('OrderComment_Model');
        $this->load->model('GameLevel_Model');
        $this->load->model('God_Model');
        //获取用户uid  一定不要这样写
//      $this->user_id = $this->getUserId();
    }

    /**
     * 发送短信,将验证码写入redis,10分钟有效
     * @author  guochao
     */
    public function sendSms()
    {
        $mobile = $this->input->post('mobile');
        if (!isMobile($mobile)) $this->responseToJson(502, '手机格式错误');
        $key = "LAST_SMSCODE_{$mobile}";
        if ($this->cache->redis->get($key)) $this->responseToJson(502, '你已发送验证码，请勿频繁操作，该验证码十分钟内有效!');

//        $code = rand(100000, 999999);
        $code = '123456';

        $response = $this->sms->sendSms("猪游纪", "SMS_109490433", $mobile, ['code' => $code]);
        log_message('info', 'response:' . json_encode($response));

        $this->cache->redis->save($key, $code, 600);

        if (isset($response->Code) && $response->Code == 'OK') {
            $this->cache->redis->save($key, $code, 600);
            $this->responseToJson(200, '发送成功');
        } else {
            $this->responseToJson(502, '发送失败');
        }
    }

    /**
     * 用户绑定手机号
     * @author  guochao
     */
    public function bindingMobile()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $mobile = $this->input->post('mobile');
        $code = $this->input->post('code');
        if (!$mobile) $this->responseToJson(502, 'mobile参数缺少');
        if (!isMobile($mobile)) $this->responseToJson(502, '手机格式错误');
        if (strlen($code) != 6) $this->responseToJson(502, '验证码错误');
        //验证码校验
        $key = "LAST_SMSCODE_{$mobile}";
        $redis_code = $this->cache->redis->get($key);
        if (!$redis_code) $this->responseToJson(502, '验证码已过期');
        if ($redis_code != $code) $this->responseToJson(502, '验证码错误');
        //用户绑定手机号判定
        $User_Model = new User_Model();
        $user_data = $User_Model->getUserById($user_id);
        if (!$user_data) $this->responseToJson(502, '该用户还没注册');
        if (isset($user_data['mobile']) && $user_data['mobile']) $this->responseToJson(502, '该用户已经绑定手机号');
        //绑定手机号
        if ($User_Model->update(['id' => $user_id], ['mobile' => $mobile, 'update_time' => date('Y-m-d H:i:s')])) {
            $this->responseToJson(200, '绑定成功');
        } else {
            $this->responseToJson(502, '绑定失败');
        }
    }


    /**
     * 用户账户微信充值
     * @author  guochao
     */
    public function recharge()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $money = $this->input->post('money');
        if (!$money || !is_numeric($money) || strstr($money, '.'))
            $this->responseToJson(502, '金额错误');

        $user_data = $this->User_Model->getUserById($user_id);
        if (!isset($user_data['openid']) || !$user_data['openid'])
            $this->responseToJson(502, '该用户还未注册');

        $openid = $user_data['openid'];
        $original_available_balance = $user_data['available_balance'];
        $out_trade_no = uuid();
        $attributes = [
            'trade_type' => 'JSAPI',
            'body' => '猪游纪账户充值',
            'detail' => '用户id:' . $user_id . '|账户充值',
            'out_trade_no' => $out_trade_no,
            'total_fee' => intval($money * 100), // 单位：分
            'notify_url' => base_url() . 'api/comm/payNotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid' => $openid // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];

        $this->db->trans_begin();
        try {
            $this->UserCashJournal_Model->insert(['out_trade_no' => $out_trade_no, 'user_id' => $user_id,
                'trade_type' => 1, 'money' => $money, 'inorout' => 1, 'pay_type' => 2, 'recharge_status' => 1,
                'original_available_balance' => $original_available_balance, 'create_time' => date('Y-m-d H:i:s')]);

            $order = new \EasyWeChat\Payment\Order($attributes);
            $payment = $this->wechat->payment;
            $result = $payment->prepare($order);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                $this->db->trans_commit();
                $prepayId = $result->prepay_id;
                $data = $payment->configForJSSDKPayment($prepayId);
                $this->responseToJson(200, '创建成功', ['weixin_pay' => $data, 'out_trade_no' => $out_trade_no]);
            } else {
                throw new Exception($result->return_msg);
            }
        } catch (\Exception $exception) {
            $this->db->trans_rollback();
            log_message('error', '创建预充值订单接口异常' . $exception->getMessage());
            $this->responseToJson(502, $exception->getMessage());
        }
    }

    //微信异步通知
    public function payNotify()
    {
        log_message('info', '微信异步通知接口时间：' .date('Y-m-d H:i:s'));

        $response = $this->wechat->payment->handleNotify(function ($notify, $successful) {
            log_message('info', '微信异步通知接口返回数据：' . json_encode($notify));
            $out_trade_no = $notify->out_trade_no;
            $userCashJournal = $this->UserCashJournal_Model->scalarBy(['out_trade_no' => $out_trade_no]);
            if (!$userCashJournal) return 'recharge order is not exist';

            //已处理
            if ($userCashJournal['recharge_status'] != 1) return true;
            //接口返回订单金额
            if ((100 * $userCashJournal['money']) != $notify->total_fee) {
                log_message('error', '微信异步通知接口返回订单金额不对');
                return false;
            }

            $user_data = $this->User_Model->getUserById($userCashJournal['user_id']);
            //用户是否支付
            if ($successful) {  //支付成功
                $this->db->trans_begin();
                $current_available_balance = $user_data['available_balance'] + $userCashJournal['money'];
                //用户账户加钱
                $res_1 = $this->User_Model->update(['id' => $userCashJournal['user_id']],
                    ['available_balance' => $current_available_balance, 'update_time' => date('Y-m-d H:i:s')]);
                //更新用户资金流水状态
                $res_2 = $this->UserCashJournal_Model->update(['out_trade_no' => $out_trade_no],
                    ['recharge_status' => 2, 'transaction_id' => $notify->transaction_id,
                        'current_available_balance' => $current_available_balance, 'update_time' => date('Y-m-d H:i:s')]);

                if ($res_1 && $res_2) {
                    $this->db->trans_commit();
                    return true;
                } else {
                    log_message('error', '充值更新数据异常');
                    $this->db->trans_rollback();
                }
            } else {
                $this->UserCashJournal_Model->update(['out_trade_no' => $out_trade_no],
                    ['recharge_status' => 3, 'update_time' => date('Y-m-d H:i:s')]);
                log_message('error', '微信异步用户支付失败');
                return 'user not pay success';
            }
        });

        return $response;
    }

    /**
     * 该方法实现从微信服务器拉取临时上传素材到本服务器
     */
    public function getQiniuUrl()
    {
        $serverId = $this->input->post('serverId');
        if (!empty($serverId)) {
            $temporary = $this->wechat->material_temporary;
            $content = $temporary->getStream($serverId);
            file_put_contents('/data/api.eachfight.com/public/wxUploads/' . $serverId . '.jpg', $content);
            $picpath = '/data/api.eachfight.com/public/wxUploads/' . $serverId . '.jpg';
            // 将图片上传到七牛
            require_once APPPATH . 'third_party/Qiniu-7.0.7/autoload.php';
            $this->output->enable_profiler(false);
            // qiniu账号
            $accessKey = config_item('qiniu.access_key');
            $secretKey = config_item('qiniu.secret_key');
            // 构建鉴权对象
            $auth = new Qiniu\Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config_item('qiniu.bucket');
            // 上传到七牛后保存的文件名
            $key = date("Ymd") . "/" . $serverId . ".png";
            // 生成上传 Token
            $policy = array(
                'scope' => $bucket . ":" . $key,
                'insertOnly' => 0,
            );
            $token = $auth->uploadToken($bucket, $key, 3600, $policy);
            // 要上传文件的本地路径
            $filePath = $picpath;
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new Qiniu\Storage\UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                $this->responseToJson(502, "上传失败");
                var_dump($err);
            } else {
                //unlink('/data/api.eachfight.com/public/wxUploads/' . $serverId . '.jpg');
                $qiniuUrl = config_item('photo.domain') . $key;
                $this->responseToJson(200, "上传成功", ['picUrl' => $qiniuUrl]);
            }
        } else {
            $this->responseToJson(502, "serverId为空");
        }
    }

    /**
     * 个人中心  兼容用户端/大神端
     * @author  guochao
     */
    public function userCenter()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $type = $this->input->post('type', 1);  //1=>用户端 2=>大神端
        if (!in_array($type, [1, 2]))
            $this->responseToJson(502, "type参数错误");
        //我的个人信息 钱包
        $user_info_data = $this->User_Model->getUserById($user_id);
        $user_info = [];
        if ($user_info_data) {
            $user_info = ['headimg_url' => $user_info_data['headimg_url'], 'nickname' => $user_info_data['nickname'],
                'gender' => user_gender()[$user_info_data['gender']], 'total_balance' => $user_info_data['available_balance'] + $user_info_data['freeze_balance'],
                'available_balance' => $user_info_data['available_balance'], 'freeze_balance' => $user_info_data['freeze_balance'],
                'withdrawal_limit' => $user_info_data['withdrawal_limit'], 'weixin_url' => $user_info_data['weixin_url']];
        }
        //帐户明细
        $user_cash_data = $this->UserCashJournal_Model->fetchAll(['user_id' => $user_id]);
        $user_cash = [];
        if ($user_cash_data) {
            foreach ($user_cash_data as $key => $val) {
                $user_cash[$key]['trade_type'] = trade_type()[$val['trade_type']] ?? '';
                if ($val['trade_type'] == 4) {
                    $user_cash[$key]['status'] = withdraw_status()[$val['withdraw_status']];
                } elseif ($val['trade_type'] == 1) {
                    $user_cash[$key]['status'] = recharge_status()[$val['recharge_status']];
                } else {
                    $user_cash[$key]['status'] = '已完成';
                }

                $user_cash[$key]['money'] = $val['money'];
                $user_cash[$key]['create_time'] = $val['create_time'];
                $user_cash[$key]['inorout'] = $val['inorout'];
            }
        }

        //订单列表  1=>用户下单列表  2=>大神接单列表
        $select_type = ($type == 1) ? 'user_id' : 'god_user_id';
        $order_list_data = $this->Order_Model->fetchAll([$select_type => $user_id]);
        $order_total = count($order_list_data);
        $order_list = [];
        if ($order_list_data) {
            foreach ($order_list_data as $key => $val) {
                $order_list[$key]['create_time'] = $val['create_time'];
                $order_list[$key]['status'] = $this->changeStatus($val['status']);
                $order_list[$key]['game_type'] = game_type()[$val['game_type']];
                $order_list[$key]['game_zone'] = game_zone()[$val['game_zone']];
                $game_level = $this->GameLevel_Model->getGameLevelName($val['game_level_id']);
                $order_list[$key]['game_level'] = $game_level->game_level;
                $order_list[$key]['game_mode'] = game_mode()[$val['game_mode']];
                $order_list[$key]['order_fee'] = $val['order_fee'];
                $order_list[$key]['game_num'] = $val['game_num'];
                $order_list[$key]['actual_victory'] = $val['actual_victory'];
                $order_comment = $this->OrderComment_Model->scalarBy(['order_id' => $val['id']]);//评价星数
                $order_list[$key]['star_num'] = $order_comment['star_num'] ?? '';
                $order_list[$key]['id'] = $val['id'];
            }
        }

        //大神的游戏数据
        $god_game = [];
        if ($type == 2) {
            $god = $this->God_Model->fetchAll(['user_id' => $user_id, 'status' => 1]);
            if ($god) {
                foreach ($god as $val) {
                    $game_level = $this->GameLevel_Model->getGameLevelName($val['game_level_id']);
                    $god_game[] = ['game_type' => game_type()[$val['game_type']],
                        'can_zone' => can_zone()[$val['can_zone']], 'game_level' => $game_level->game_level];
                }
            }
        }

        $this->responseToJson(200, '获取成功', ['user_info' => $user_info, 'user_cash' => $user_cash,
            'order_total' => $order_total, 'order_list' => $order_list, 'god_game' => $god_game]);
    }

    //订单状态  已取消  进行中  申诉中  已完成
    private function changeStatus($status)
    {
        switch ($status) {
            case 2:
            case 4:
                return '已取消';
                break;

            case 8:
                return '申诉中';
                break;

            case 9:
                return '已完成';
                break;

            default:
                return '进行中';
        }
    }

    /**
     * 大神收入提现
     * @author  guochao
     */
    public function godWithdraw()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $user_data = $this->User_Model->getUserById($user_id);
        if (!isset($user_data['openid']) || !$user_data['openid'])
            $this->responseToJson(502, '该用户还未注册');

        $god = $this->God_Model->scalarBy(['user_id' => $user_id, 'status' => 1]);
        if (!$god) {
            $this->responseToJson(502, '你不是大神或者大神身份被冻结,无法提现');
        }

        $money = $this->input->post('money');
        if (!$money || !is_numeric($money) || strstr($money, '.'))
            $this->responseToJson(502, '输入提现金额错误');

        if ($money > intval($user_data['withdrawal_limit']))  //取整
            $this->responseToJson(502, '提现金额不可大于提现额度');

        $this->db->trans_begin();
        try {
            //插入提现记录
            $result_1 = $this->UserCashJournal_Model->insert(['user_id' => $user_id, 'trade_type' => 4,
                'money' => $money, 'inorout' => 2, 'pay_type' => 1, 'withdraw_status' => 1,
                'create_time' => date('Y-m-d H:i:s'), 'original_available_balance' => $user_data['available_balance'],
                'current_available_balance' => $user_data['available_balance'] - $money]);
            //更新用户表  减少可用和可提现额度
            $result_2 = $this->User_Model->update(['id' => $user_id], ['available_balance' => $user_data['available_balance'] - $money,
                'withdrawal_limit' => $user_data['withdrawal_limit'] - $money, 'update_time' => date('Y-m-d H:i:s')]);

            if ($result_1 && $result_2) {
                $this->db->trans_commit();
                $this->responseToJson(200, '提现提交成功,等待后续处理结果');
            } else {
                $this->db->trans_rollback();
                throw new \Exception('提现提交异常');
            }
        } catch (\Exception $exception) {
            $this->db->trans_rollback();
            log_message('error', '大神收入提现接口异常' . $exception->getMessage());
            $this->responseToJson(502, $exception->getMessage());
        }
    }

    /**
     * 用户添加或更新自己的二维码
     * @author  guochao
     */
    public function changeWeixinUrl()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $weixin_url = $this->input->post('weixin_url');
        if (!$weixin_url)
            $this->responseToJson(502, 'weixin_url参数错误');

        $user_data = $this->User_Model->getUserById($user_id);
        if($user_data['weixin_url'] == $weixin_url)
            $this->responseToJson(502, '该图片与原图一样,请不要反复上传');

        $result = $this->User_Model->update(['id' => $user_id], ['weixin_url' => $weixin_url,
            'update_time' => date('Y-m-d H:i:s')]);
        if ($result) {
            $this->responseToJson(200, '提交成功');
        } else {
            $this->responseToJson(502, '提交失败');
        }
    }

}
