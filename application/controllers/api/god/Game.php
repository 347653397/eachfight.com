<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends CI_Controller
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
        $this->load->helper('used_helper');
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    public function completeGame(){

        // 订单ID
        $orderId = $this->input->post('order_id');
        if($this->user_id && !empty($orderId)){
            // 大神身份验证
            $godInfo = $this->God_Model->scalarBy(['user_id' => $this->user_id, 'status' => 1]);
            if(!empty($godInfo)){
                $orderInfo = $this->Order_Model->scalar($orderId);
                if(!empty($orderInfo)){
                    if($orderInfo['god_user_id'] == $this->user_id){
                        // 变更订单表状态
                        $order = array('status' => ORDER_COMPLETE_GAME);
                        $this->Order_Model->update(["id"=>$orderId],$order);
                        $this->responseToJson(200, '获取成功', $this->Order_Model->scalar($orderId));
                    }else{
                        $this->responseToJson(502, '无法操作此订单');
                    }
                }else{
                    $this->responseToJson(502, '该订单不存在');
                }
            }else{
                $this->responseToJson(502, '非大神无法操作');
            }
        }
    }
}
