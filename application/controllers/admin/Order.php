<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 订单管理
 */
class Order extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->helper('used_helper');
        $this->load->model('Order_Model');
        $this->load->model('User_Model');
    }

	public function index()
	{
		$where=[];
		$order=$this->Order_Model->fetchAll($where);
		foreach ($order as $key => $value) {
			//查出订单用户
			$userid=$value['user_id'];
			$user=$this->User_Model->scalar($userid);
			$nickname=isset($user['nickname'])?$user['nickname']:"";
			// dump($nickname);exit;
			$order[$key]['username']=emoji_to_string($nickname);

			//查出大神用户
			$goduserid=$value['user_id'];
			$goduser=$this->User_Model->scalar($goduserid);
			$order[$key]['godusername']=isset($goduser['nickname'])?emoji_to_string($goduser['nickname']):"";
		}
		// dump($order);exit;
		$data['count'] = count($order);
		$data['list']=$order;
		$this->load->view('admin/order/index',$data);
	}
}