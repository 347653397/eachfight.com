<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神战绩接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GodBattleRecord extends MY_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('OrderRecord_Model', 'orderRecord');
        $this->load->model('Order_Model', 'order');
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 查询大神战绩
     */
    public function index_get()
    {
        $id = $this->uri->segment(3);
        if(!empty($id)){
            if (!is_numeric($id)){
                $this->responseJson(502,'战绩ID参数错误');
            }
            $data = $this->orderRecord->scalar($id);
        }else{
            $data = $this->orderRecord->fetchAll();
        }
        if(!empty($data)){
            $this->responseJson(200, '数据获取成功', $data);
        }else{
            $this->responseJson(502, '数据获取失败');
        }

    }

    /**
     * 大神提交战绩
     */
    public function index_post(){
        $flag = false;
        $post = $this->input->post();
        $this->validPost($post);
        // 是否已经提交战绩
        if($this->orderRecord->checkExist($post['order_id'])){
            $this->responseJson(401, '该订单已经提交过战绩了');
        }
        // 是否订单是否存在
        if(!$this->orderRecord->checkOrder($post['order_id'])){
//            pp($this->db->last_query());exit();
            $this->responseJson(401, '该订单不存在');
        }
        // 判断提交资格
        $orderInfo = $this->order->scalar($post['order_id']);
        if($orderInfo['god_user_id'] != $this->user_id){
            $this->responseJson(401, '无权限对此订单提交战绩');
        }
        // 提交局数不能大于订单局数
        if($post['victory_num'] > $orderInfo['game_num']){
            $this->responseJson(401, "胜利局数不能大于订单局数");
        }
        $dataField = ['order_id'=>'订单ID', 'victory_num'=>'胜利局数'];
        $data = [];
        foreach ($dataField as $key=>$val) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // 提交数据
        $data['create_time'] = date("Y-m-d H:i:s",time());
        $id = $this->orderRecord->insert($data,true);
        if($id){
            // 变更订单表状态
            $order = array('status' => ORDER_GOD_SUB_ORDER, 'sumbit_time' => $data['create_time']);
            $this->order->update(["id"=>$post['order_id']],$order);
            $return = $data+$order;
            $this->responseJson(200, '数据写入成功', $return);
        }else{
            $this->responseJson(200, '数据写入失败');
        }
    }

    /**
     * 大神提交数据验证
     * @param $data
     */
    private function validPost($data)
    {
        $require = ['order_id'=>'订单ID', 'victory_num'=>'胜利局数'];
        foreach ($require as $key => $val) {
            // 非空验证
            if (!isset($data[$key]) || empty($data[$key])) {
                $this->responseJson(401, $val.' 不能为空');
                break;
            }
        }
        // 数据格式
        if($data['order_id'] < 0 || !is_numeric($data['order_id'])){
            $this->responseJson(401, ' 订单数据类型错误');
        }
        if($data['victory_num'] > 3 || $data['victory_num'] < 0 || !is_numeric($data['victory_num'])){
            $this->responseJson(401, ' 胜利局数数据类型错误');
        }
    }
}
