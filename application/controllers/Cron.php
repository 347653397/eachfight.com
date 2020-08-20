<?php
/**
 * 定时任务
 * Created by PhpStorm.
 * User: guochao
 * Date: 2018/1/24
 * Time: 下午8:09
 */

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Order_Model');

        $this->load->helper('http_helper');
        $this->load->helper('used_helper');
    }

    public function index()
    {
        dump(100);
        exit;
    }

    /**
     * 每分钟执行一次
     * 超过150s无人抢单，自动取消订单
     */
    public function autoCanceOrder()
    {
        $orders = $this->Order_Model->fetchAll(['status' => 1]);
        $update_order = [];
        if ($orders) {
            foreach ($orders as $val) {
                if ((strtotime($val['create_time']) + 150) < time()) {
                    if ($this->Order_Model->update(['id' => $val['id']],
                        ['status' => 2, 'update_time' => date('Y-m-d H:i:s')])) {
                        $update_order[] = $val['id'];
                    }
                } else {
                    log_message('error', '异常-超过150s无人抢单自动取消订单id:' . $val['id']);
                }
            }
        }

        if ($update_order) {
            log_message('info', '超过150s无人抢单自动取消订单ids:' . json_encode($update_order));
        }
    }

    /**
     * 每10分钟执行一次
     * 大神提交战绩 24小时后 用户不操作的话 自动根据大神提交的战绩 结算收入
     */
    public function autoFinishOrder()
    {
        $orders = $this->Order_Model->fetchAll(['status' => 7]);
        $update_order = [];
        if ($orders) {
            foreach ($orders as $val) {
                if ((strtotime($val['sumbit_time'] + 86400) < time())) {
                    $url = base_url() . 'api/user/userOperateOrder';

                    $order = $this->Order_Model->scalar($val['id']);
                    $user_id = $order['user_id'];

                    $param = ['order_id' => $val['id'], 'type' => 4, 'user_id' => $user_id];
                    $sign = verify(['order_id' => $val['id'], 'user_id' => $user_id]);
                    $data = array_merge($param, ['admin_sign' => $sign]);

                    $res =  curlQuery($url, true, $data);
                    $result = json_decode($res,true);
                    if(isset($result['status']) && ($result['status'] == 200)){
                        $update_order[] = $val['id'];
                    }
                }
            }
        }

        if ($update_order) {
            log_message('info', '大神提交战绩24小时后用户不操作的话自动根据大神提交的战绩结算收入ids:' . json_encode($update_order));
        }
    }

}