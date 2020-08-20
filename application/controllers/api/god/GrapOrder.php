<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神抢单接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GrapOrder extends MY_Controller
{
    const GRAP_KEY = "GrapOrder_";

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('OrderRecord_Model');
        $this->load->model('GameLevel_Model');
        $this->load->model('God_Model', 'god');
        $this->load->model('User_Model', 'user');
        $this->load->model('Order_Model', 'order');
        $this->load->model('OrderLog_Model', 'orderlog');
        $this->load->helper('used_helper');

        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 大神抢单
     */
    public function index_post()
    {
        // 订单ID
        $orderId = $this->input->post('order_id');
        // 判断订单状态，是否可以抢单
        $orderInfo = $this->order->scalar($orderId);
        if(!empty($orderInfo) && !empty($this->user_id)){
                // 大神身份验证
                $godInfo = $this->god->scalarBy(['user_id' => $this->user_id, 'status' => 1]);
                if(!empty($godInfo)){
                    if($this->cache->redis->get(self::GRAP_KEY.$orderId)){
                        $this->responseJson(502, '手慢了，订单已经被抢');
                    }else{
                        $this->cache->redis->save( self::GRAP_KEY.$orderId, $this->user_id);
                        // 变更订单状态
                        $new_info = array(
                            'status'=>ORDER_GOD_GRAB,
                            'god_user_id'=>$this->user_id,
                            'grab_time'=>date('Y-m-d H:i:s'),
                        );
                        $this->db->trans_begin();
                        $order_insert_id = $this->order->update(['id'=>$orderId],$new_info);
                        $log_data = array(
                            'order_id'=>$orderId,
                            'begin_status'=>ORDER_CANCER_NO_ACCEPT,
                            'end_status'=>ORDER_GOD_GRAB,
                            'remark'=>"大神ID".$this->user_id."抢单成功",
                            'create_time'=>date('Y-m-d H:i:s'),
                        );
                        $log_insert_id = $this->orderlog->insert($log_data, true);
                        if($order_insert_id && $log_insert_id){
                            $this->db->trans_commit();
                            $return_data['god_id'] = $this->user_id;
                            $this->responseJson(200, '抢单成功', $return_data);
                        }else{
                            $this->db->trans_rollback();
                            $this->cache->redis->delete(self::GRAP_KEY.$orderId);
                            $this->responseJson(502, '抢单失败');
                        }
                    }
                }else{
                    $this->responseJson(502, '只有认证大神才能抢单');
                }
        }else{
            $this->responseJson(502, '订单不存在');
        }
    }

    /**
     * 大神订单状态
     */
    public function index_get(){
        // 订单ID
        $order_id = $this->input->get('order_id');
        // 订单信息
        $orderData = $this->order->scalar($order_id);
        if(!empty($orderData)){
//            if($this->user_id == $orderData['god_user_id']){
                $play_status = $this->getGodPlayStatus($this->user_id, $orderData['god_user_id'], $orderData['status']);
                // 订单所需要展示的信息
                if ($order_id) {
                    $orderInfo = array(
                        'game_type'=>$orderData['game_type'],
                        'game_mode'=>$orderData['game_mode'],
                        'game_zone'=>$orderData['game_zone'],
                        'game_num'=>$orderData['game_num'],
                        'order_fee'=>$orderData['order_fee'],
                        'game_level_id'=>$orderData['game_level_id'],
                        'remark'=>$orderData['remark'],
                        'device'=>$orderData['device'],
                    );
                    // 如果订单已完成，则追加战绩数据
                    if($play_status == 8){
                        $OrderRecord_Model = $this->OrderRecord_Model->scalarBy(['order_id' => $order_id]);
                        $victory_num = $OrderRecord_Model['victory_num'];
                        $orderInfo['order_id'] = $order_id;
                        $orderInfo['victory_num'] = $victory_num;
                    }
                }
                //获取用户信息
                $user_data = $this->user->getUserById($orderData['user_id']);
                //返回用户信息
                $user_info = array(
                    'nickname'=>emoji_to_string($user_data['nickname']),
                    'headimg_url'=>$user_data['headimg_url'],
                    'mobile'=>$user_data['mobile'],
                    'gender'=>$user_data['gender'],
                    'weixin_url'=>$user_data['weixin_url'],
                );
                //存在大神时获取大神信息
                $god_info = ($order_id && $orderData['god_user_id']) ?
                    $this->getGodInfo($orderData['god_user_id'], $orderData['game_type']) : [];
                $data = ['play_status' => $play_status, 'user_info' => $user_info, 'god_info' => ($play_status == 1) ? [] : $god_info,
                    'order_info' => $orderInfo];

                $this->responseJson(200, '获取成功', $data);
//            }

        }else{
            $this->responseJson(502, '该订单不存在');
        }

    }

    //根据用户id及游戏类型获取大神的信息
    private function getGodInfo($user_id, $game_type)
    {
        $god_info = $this->god->getGodInfo($user_id, $game_type);
        $user_info = $this->user->getUserById($user_id);

        $result = ['headimg_url' => $user_info['headimg_url'], 'nickname' => emoji_to_string($user_info['nickname']),
            'gender' => $user_info['gender'], 'mobile' => $user_info['mobile'], 'weixin_url' => $user_info['weixin_url'],
            'order_num' => $god_info['order_num'], 'comment_score' => $god_info['comment_score']];

        $game_level = $god_info['game_level_id'] ? $this->GameLevel_Model->getGameLevelName($god_info['game_level_id']) : '';

        return array_merge($result, ['game_level' => $game_level->game_level]);
    }

    /**
     * 根据大神用户id及订单id获取 获取大神订单状态
     * @param $user_id
     * @param $order_id
     * @return bool|int
     */
    private function getGodPlayStatus($user_id, $god_user_id, $status)
    {
        //1=>等待抢单 2=>订单已取消 3=>抢单成功待用户准备  4=>已被抢  5=>正在游戏中，待完成游戏
        // 6=>已完成游戏，待提交战绩 7=>已提交战绩,待完成待定  8=>用户发起申诉，待完成订单 9=>订单完成
        if($status == 1){
            $play_status = 1;   //等待抢单
        }elseif ($status == 2 || $status == 4){
            $play_status = 2;   //订单已取消
        }else{
            if ( $god_user_id == $user_id) {
                $play_status = $status;
            } else {
                $play_status = 4;  //已被抢
            }
        }

        return $play_status;
    }

}
