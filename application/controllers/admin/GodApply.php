<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 订单管理
 */
class GodApply extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->helper('used_helper');
        $this->load->model('User_Model');
        $this->load->model('GodApply_Model');
        $this->load->model('GameLevel_Model');
    }

	public function index()
	{
		$where=[];
		$godapply=$this->GodApply_Model->fetchAll($where);
		foreach ($godapply as $key => $value) {
			//查出用户信息
			$userid=$value['user_id'];
			$user=$this->User_Model->scalar($userid);
			$godapply[$key]['nickname']=isset($user['nickname'])?emoji_to_string($user['nickname']):"";
			//查出段位信息
			$levelid=$value['game_level_id'];
			$gamelevel=$this->GameLevel_Model->scalar($levelid);
			$godapply[$key]['game_level']=isset($gamelevel['game_level'])?emoji_to_string($gamelevel['game_level']):"";
		}
		$data['count'] = count($godapply);
		$data['list']=$godapply;
		$this->load->view('admin/godapply/index',$data);
	}
}