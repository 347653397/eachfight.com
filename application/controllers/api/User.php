<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->model('Order_Model');
        $this->load->model('God_Model');
        $this->load->model('OrderRecord_Model');
        $this->load->model('UserCashJournal_Model');
        $this->load->model('OrderComment_Model');
        $this->load->model('OrderShensu_Model');

        $this->load->library('form_validation');
        $this->load->helper('used_helper');

        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 获取用户游戏及手机绑定状态
     */
    public function index()
    {
        try {
            //获取当前用户游戏状态
            $user_order = $this->Order_Model->getUserOrder($this->user_id);
            $play_status = $this->getUserPlayStatus($user_order->status ?? '');

            //获取用户信息
            $user_data = $this->User_Model->getUserById($this->user_id);
            //手机绑定状态
            $user_info = ['mobile_bind' => $user_data['mobile'] ? 1 : 0, 'available_balance' => $user_data['available_balance']];

            $order_id = $user_order->id ?? '';
            //存在大神时获取大神信息
            $god_info = ($order_id && $user_order->god_user_id) ?
                $this->getGodInfo($user_order->god_user_id, $user_order->game_type) : [];

            $victory_num = '';
            if ($order_id) {
                $OrderRecord_Model = $this->OrderRecord_Model->scalarBy(['order_id' => $user_order->id]);
                $victory_num = $OrderRecord_Model['victory_num'] ?? '';
            }

            $order_info = ['order_id' => $order_id, 'victory_num' => $victory_num];

            if ($order_id && ($user_order->status >= 6)) {  //申诉订单详情
                $game_level = $this->GameLevel_Model->getGameLevelName($user_order->game_level_id);
                $data = ['play_status' => $play_status, 'user_info' => $user_info, 'god_info' => ($play_status == 1) ? [] : $god_info,
                    'order_info' => array_merge($order_info, ['game_type' => game_type()[$user_order->game_type],
                        'game_zone' => game_zone()[$user_order->game_zone], 'game_level' => $game_level->game_level,
                        'game_mode' => game_mode()[$user_order->game_mode], 'is_except' => is_except()[$user_order->is_except],
                        'game_num' => $user_order->game_num, 'order_fee' => $user_order->order_fee])];
            } else {
                $data = ['play_status' => $play_status, 'user_info' => $user_info, 'god_info' => ($play_status == 1) ? [] : $god_info,
                    'order_info' => $order_info];
            }

            $this->responseToJson(200, '获取成功', $data);
        } catch (\Exception $exception) {
            $this->responseToJson(200, $exception->getMessage());
        }
    }

    /**
     * 获取段位价格配置
     */
    public function getGameLevel()
    {
        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);
    }


    /**
     * 用户下单
     */
    public function createOrder()
    {
        $params = $this->input->post();
        //参数校验
        $this->form_validation->set_data($params);
        if ($this->form_validation->run('create_order') == false) {
            $errors = array_values($this->form_validation->error_array());
            $this->responseToJson(502, array_shift($errors));
        }
        //排除游戏中
        $user_order = $this->Order_Model->getUserOrder($this->user_id);
        $play_status = $this->getUserPlayStatus($user_order->status ?? '');
        if ($play_status != 1) $this->responseToJson(502, '该用户有一笔未完成的订单');
        //每局价格
        $GameLevel_Model = $this->GameLevel_Model->scalar($params['game_level_id']);
        $one_price = $actual_price = $GameLevel_Model['one_price'];
        //是否计胜负
        $discount_rax = 0.3;
        if ($params['is_except'] == 2) {  //不计优惠
            $actual_price = $one_price * (1 - $discount_rax);
        }
        //订单金额
        $order_fee = $actual_price * $params['game_num'];
        //金额判定
        $user_info = $this->User_Model->getUserById($this->user_id);
        if ($order_fee > $user_info['available_balance'])
            $this->responseToJson(502, '该用户账户余额不足');

        //入库给大神推送微信模板消息(结算才扣用户的钱)
        $insert_id = $this->Order_Model->insert([
            'user_id' => $this->user_id,
            'game_type' => $params['game_type'],
            'game_mode' => $params['game_mode'],
            'is_except' => $params['is_except'],
            'device' => $params['device'],
            'game_zone' => $params['game_zone'],
            'game_level_id' => $params['game_level_id'],
            'one_price' => $one_price,
            'actual_price' => $actual_price,
            'game_num' => $params['game_num'],
            'discount_rax' => $discount_rax,
            'order_fee' => $order_fee,
            'remark' => htmlspecialchars($params['remark']),
            'create_time' => date('Y-m-d H:i:s')
        ], true);
        if ($insert_id) {
            //订单信息
            $game_level = $GameLevel_Model['game_level'];
            $order_info = game_type()[$params['game_type']] . '|' . game_mode()[$params['game_mode']] . '|'
                . device()[$params['device']] . game_zone()[$params['game_zone']]
                . '|' . $game_level . '|' . $params['game_num'] . '局';
            //给满足条件的大神推送新订单消息
            $god_openids = ['oDfTV1C71uJfWGaI5vcMWrktCg3c'];
//            $god_openids = $this->getSendNoticeGods($params);
            $this->sendNotice($god_openids, $order_fee, $order_info, $insert_id);
            $this->responseToJson(200, '下单成功');
        } else {
            $this->responseToJson(502, '下单失败');
        }
    }

    /**
     * 用户操作订单 type 1:未接取消  2:已接取消 3:用户准备  4:用户确认付款
     */
    public function userOperateOrder()
    {
        $user_id = $this->user_id;
        $params = $this->input->post();
        //参数校验
        $this->form_validation->set_data($params);
        if ($this->form_validation->run('operate_order') == false) {
            $errors = array_values($this->form_validation->error_array());
            $this->responseToJson(502, array_shift($errors));
        }
        $type = $params['type'];
        $order_id = $params['order_id'];
        //状态配对
        $status_arr = [1 => 2, 2 => 3, 3 => 3, 4 => 5];   //操作类型=>游戏状态
        $order = $this->Order_Model->scalarBy(['id' => $order_id]);
        if ($order['user_id'] != $user_id) $this->responseToJson(502, '非该用户下的订单');
        $play_status = $this->getUserPlayStatus($order['status']);
        if ($status_arr[$type] != $play_status)
            $this->responseToJson(502, '订单状态异常');

        $change_arr = [1 => 2, 2 => 4, 3 => 5, 4 => 9]; //操作类型=>操作后状态
        if (in_array($type, [1, 2, 3])) {
            //用户准备发客服消息
            if ($type == 3) {
                $type = 3;
            }
            if ($this->Order_Model->update(['id' => $order_id],
                ['status' => $change_arr[$type], 'update_time' => date('Y-m-d H:i:s')])) {
                $this->responseToJson(200, '操作成功');
            } else {
                $this->responseToJson(502, '操作失败');
            }
        } else { //用户确认付款 根据战绩结算大神收入 扣用户钱 加大神钱
            $OrderRecord_Model = $this->OrderRecord_Model->scalarBy(['order_id' => $order_id]);
            $victory_num = $OrderRecord_Model['victory_num'] ?? [];
            if (!$victory_num) $this->responseToJson(502, '胜利局数错误');
            if ($victory_num > $order['game_num'])
                $this->responseToJson(502, '胜利局数错误');
            $actual_price = $order['actual_price'];
            $clearinf_fee = $actual_price * $victory_num;
            $rate_money = $clearinf_fee * 0.1;
            $god_fee = $clearinf_fee * 0.9;

            $this->db->trans_begin();
            try {
                $result_1 = $this->Order_Model->update(['id' => $order_id],
                    ['status' => $change_arr[$type], 'clearinf_fee' => $clearinf_fee, 'rate_money' => $rate_money,
                        'clearing_time' => date('Y-m-d H:i:s'), 'actual_victory' => $victory_num, 'update_time' => date('Y-m-d H:i:s')]);
                //用户扣
                $user_info = $this->User_Model->getUserById($user_id);
                if (!isset($user_info['available_balance'])) {
                    throw new \Exception('用户账户余额异常');
                }
                $user_available_balance = $user_info['available_balance'];
                if (!($user_available_balance - $clearinf_fee)) {
                    throw new \Exception('用户账户余额不够');
                }
                $user_current_available_balance = $user_available_balance - $clearinf_fee;
                $result_2 = $this->User_Model->update(['id' => $user_id],
                    ['available_balance' => $user_current_available_balance, 'update_time' => date('Y-m-d H:i:s')]);

                $result_3 = $this->UserCashJournal_Model->insert(['user_id' => $user_id, 'trade_type' => 2, 'money' => $clearinf_fee,
                    'inorout' => 2, 'pay_type' => 1, 'out_id' => $order_id, 'original_available_balance' => $user_available_balance,
                    'current_available_balance' => $user_current_available_balance, 'create_time' => date('Y-m-d H:i:s')]);

                //大神加 更新可提现额度 更新成功接单次数
                $god_info = $this->User_Model->getUserById($order['god_user_id']);
                if (!isset($god_info['available_balance'])) {
                    throw new \Exception('用户账户余额异常');
                }
                $god_available_balance = $god_info['available_balance'];
                $withdrawal_limit = $god_info['withdrawal_limit'] + $god_fee;
                $god_current_available_balance = $god_available_balance + $god_fee;
                $result_4 = $this->User_Model->update(['id' => $order['god_user_id']],
                    ['available_balance' => $god_current_available_balance, 'withdrawal_limit' => $withdrawal_limit,
                        'update_time' => date('Y-m-d H:i:s')]);

                $result_5 = $this->UserCashJournal_Model->insert(['user_id' => $order['god_user_id'], 'trade_type' => 3,
                    'money' => $god_fee, 'inorout' => 1, 'pay_type' => 1, 'out_id' => $order_id,
                    'original_available_balance' => $god_available_balance, 'current_available_balance' => $god_current_available_balance,
                    'create_time' => date('Y-m-d H:i:s')]);

                //更新成功接单次数
                $result_6 = $this->God_Model->updateOrderNum(['user_id' => $order['god_user_id'], 'game_type' => $order['game_type']]);

                if ($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_6) {
                    $this->db->trans_commit();
                    $this->responseToJson(200, '操作成功');
                } else {
                    throw new \Exception('数据结算异常');
                }
            } catch (\Exception $exception) {
                $this->db->trans_rollback();
                log_message('error', '数据结算异常接口异常' . $exception->getMessage());
                $this->responseToJson(502, $exception->getMessage());
            }
        }
    }

    //提交评论
    public function submitComment()
    {
        $user_id = $this->user_id;
        $params = $this->input->post();
        //参数校验
        $this->form_validation->set_data($params);
        if ($this->form_validation->run('submit_comment') == false) {
            $errors = array_values($this->form_validation->error_array());
            $this->responseToJson(502, array_shift($errors));
        }
        $order_id = $params['order_id'];
        $order = $this->Order_Model->scalarBy(['id' => $order_id]);
        if ($order['user_id'] != $user_id) $this->responseToJson(502, '非该用户下的订单');
        if ($order['status'] != 9) $this->responseToJson(502, '订单状态异常');
        $order_comment = new OrderComment_Model();
        if ($order_comment->scalarBy(['order_id' => $order_id]))
            $this->responseToJson(502, '该订单已评论');

        try {
            $this->db->trans_begin();
            //插入数据
            $result_1 = $order_comment->insert(['order_id' => $order_id, 'game_type' => $order['game_type'], 'user_id' => $order['user_id'],
                'god_user_id' => $order['god_user_id'], 'star_num' => $params['star_num'],
                'context' => htmlspecialchars($params['context']), 'create_time' => date('Y-m-d H:i:s')]);
            //更新分数  总星数/总成功接单数
            $god_info = $this->God_Model->getGodInfo($order['god_user_id'], $order['game_type']);
            $all_order = $god_info['order_num'] + 1;
            $star = $order_comment->getAllStar(['god_user_id' => $order['god_user_id'],
                'game_type' => $order['game_type']]);
            $all_star = $star->all_star + 5;
            $comment_score = round($all_star / $all_order, 2);
            $result_2 = $this->God_Model->update(['user_id' => $order['god_user_id'],
                'game_type' => $order['game_type']], ['comment_score' => $comment_score,
                'update_time' => date('Y-m-d H:i:s')]);

            if ($result_1 && $result_2) {
                $this->db->trans_commit();
                $this->responseToJson(200, '评论成功');
            } else {
                $this->db->trans_rollback();
                throw new \Exception('提交评论异常');
            }
        } catch (\Exception $exception) {
            $this->responseToJson(502, $exception->getMessage());
        }
    }

    //提交订单申诉
    public function sumbitOrderShensu()
    {
        $user_id = $this->user_id;
        $params = $this->input->post();
        //参数校验
        $this->form_validation->set_data($params);
        if ($this->form_validation->run('submit_order_shensu') == false) {
            $errors = array_values($this->form_validation->error_array());
            $this->responseToJson(502, array_shift($errors));
        }
        $order_id = $params['order_id'];
        $order = $this->Order_Model->scalarBy(['id' => $order_id]);
        if(!$order) $this->responseToJson(502, '该订单不存在');
        if ($order['user_id'] != $user_id) $this->responseToJson(502, '非该用户下的订单');
        if ($order['status'] != 7) $this->responseToJson(502, '订单状态异常');
        if ($order['is_shensu']) $this->responseToJson(502, '该订单已申诉');

        $OrderRecord_Model = $this->OrderRecord_Model->scalarBy(['order_id' => $order_id]);
        if (!isset($OrderRecord_Model['victory_num']) || $params['victory_num'] >= $OrderRecord_Model['victory_num'])
            $this->responseToJson(502, '胜利局数错误');
        $order_fee = $order['order_fee'];

        $this->db->trans_begin();
        try {
            $result_1 = $this->OrderShensu_Model->insert(['order_id' => $order_id, 'record_url' => $params['record_url'],
                'victory_num' => $params['victory_num'], 'shensu_des' => htmlspecialchars($params['shensu_des']),
                'create_time' => date('Y-m-d H:i:s')]);

            $result_2 = $this->Order_Model->update(['id' => $order_id], ['status' => 8, 'is_shensu' => 1, 'update_time' => date('Y-m-d H:i:s')]);
            //减少用户账户可用余额增加用户账户冻结余额
            $user_info = $this->User_Model->getUserById($user_id);
            $result_3 = $this->User_Model->update(['id' => $user_id], ['available_balance' => $user_info['available_balance'] - $order_fee,
                'freeze_balance' => $user_info['freeze_balance'] + $order_fee]);

            if ($result_1 && $result_2 && $result_3) {
                $this->db->trans_commit();
                $this->responseToJson(200, '申诉提交成功,等待后续处理结果');
            } else {
                $this->db->trans_rollback();
                throw new \Exception('提交申诉异常');
            }

        } catch (\Exception $exception) {
            $this->db->trans_rollback();
            $this->responseToJson(502, $exception->getMessage());
        }
    }


    //根据订单状态获取用户游戏状态
    private function getUserPlayStatus($status)
    {
        switch ($status) {
            case 1:
                $play_status = 2;  //等待接单
                break;

            case 3:
                $play_status = 3;  //运行游戏(大神抢单后,用户还没有准备)
                break;

            case 5:
            case 6:
                $play_status = 4;  //运行游戏(大神抢单后,用户已准备)
                break;

            case 7:
                $play_status = 5;  //付款(大神提交战绩)
                break;

            case 2:
            case 4:
            case 8:
            case 9:
                $play_status = 1;  //去下单   未接取消/已接取消/用户发起申诉/订单完成
                break;

            default:
                $play_status = 1;  //去下单 当前没有订单
        }

        return $play_status;
    }

    //根据用户id及游戏类型获取大神的信息
    private function getGodInfo($user_id, $game_type)
    {
        $god_info = $this->God_Model->getGodInfo($user_id, $game_type);
        $user_info = $this->User_Model->getUserById($user_id);

        $result = ['headimg_url' => $user_info['headimg_url'], 'nickname' => emoji_to_string($user_info['nickname']),
            'gender' => $user_info['gender'], 'mobile' => $user_info['mobile'], 'weixin_url' => $user_info['weixin_url'],
            'order_num' => $god_info['order_num'], 'comment_score' => $god_info['comment_score']];

        $game_level = $god_info['game_level_id'] ? $this->GameLevel_Model->getGameLevelName($god_info['game_level_id']) : '';

        return array_merge($result, ['game_level' => $game_level->game_level ?? null]);
    }

    //获取满足订单条件的大神openid
    public function getSendNoticeGods(array $params)
    {
        $game_level_id = $params['game_level_id']; //游戏段位
        $game_type = $params['game_type']; //游戏类型  1=>王者荣耀   2=>英雄联盟
        $can_zone = $params['game_zone']; //可接大区   1=>不限  2=>微信区   3=>QQ区
        $can_device = $params['device']; //可接设备系统  1=>不限  2=>IOS  3=>安卓
        $user_id = $this->user_id;
        //排除自己
        $sql = "SELECT user_id FROM t_god WHERE status = ? AND user_id != ? AND game_level_id >= ? AND
        game_type = ? AND can_zone IN ? AND can_device IN ?";
        $query = $this->db->query($sql,
            array('1', $user_id, $game_level_id, $game_type, array(1, $can_zone), [1, $can_device]));
        $gods = $query->result_array();

        $data = [];
        foreach ($gods as $val) {
            //排除游戏中的大神
            $play_status = [3, 5, 6, 7, 8];
            $oneGodOrder = $this->Order_Model->getGodOrder($val['user_id']);
            if (!$oneGodOrder || !in_array($oneGodOrder->status, $play_status)) {
                $user = $this->User_Model->getUserById($val['user_id']);
                if (!isset($user['openid'])) continue;
                $data[] = $user['openid'];
            }
        }

        return $data;
    }

    //给大神推送模板消息
    private function sendNotice($god_openids, $order_fee, $order_info, $order_id)
    {
        $notice = $this->wechat->notice;
        $templateId = 'tic8-fQPEnGzK38IlyQBNsVbcesdEOtp1a_m7UUN3Kc';
        $url = 'http://weixin.eachfight.com/#/mantio/' . $order_id;  //大神端入口地址
        $data = array(
            "first" => "收到一笔新的陪练需求",
            "keyword1" => time(),
            "keyword2" => $order_fee . '元',
            "keyword3" => $order_info
        );

        $weixin_user = $this->wechat->user->batchGet($god_openids)->toArray();
        foreach ($weixin_user['user_info_list'] as $val) {
            if ($val['subscribe'] == 0) continue;
            $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($val['openid'])->send();
            log_message('info', '给大神推送模板消息opendid:' . $val['openid'] . '--' . json_encode($result));
        }

    }
}
