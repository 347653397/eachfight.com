<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神申请接口
 * Class GodBattleRecord
 * @author    fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class God extends MY_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('God_Model', 'god');
        $this->load->model('User_Model', 'user');
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 查询大神所有信息
     */
    public function index_get()
    {
        if (!empty($this->user_id)) {
            // 根据openid获取用户ID
            $userInfo = $this->user->scalar($this->user_id);
            if (!empty($userInfo)) {
                $godInfo = $this->god->scalarBy(['user_id' => $this->user_id, 'status' => 1]);
                if (!empty($godInfo)) {
                    $data = $godInfo + $userInfo;
                    $data['game_type'] = game_type()[$data['game_type']];
                    $data['game_level_id'] = $data['game_level_id'];
                    $data['can_zone'] = can_zone()[$data['can_zone']];
                    $data['can_device'] = can_device()[$data['can_device']];
                    $this->responseJson(200, '数据获取成功', $data);
                } else {
                    $this->responseJson(502, '该用户不是大神');
                }
            } else {
                $this->responseJson(502, '没有该用户信息');
            }
        } else {
            $this->responseJson(502, 'openid参数缺失');
        }
    }


}
