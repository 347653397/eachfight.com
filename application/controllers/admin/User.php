<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 订单管理
 */
class User extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->model('User_Model');
    }

	public function index()
	{
		$where=[];
		$user=$this->User_Model->fetchAll($where);
		$data['count'] = count($user);
		$data['list']=$user;
		$this->load->view('admin/user/index',$data);
	}
}