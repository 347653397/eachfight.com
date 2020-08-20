<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神申请接口
 * Class GodBattleRecord
 * @author    fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GodApply extends MY_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('GodApply_Model', 'godApply');

        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 查询大神申请
     */
    public function index_get()
    {
        $id = $this->uri->segment(3);
        if (!empty($id)) {
            if (!is_numeric($id)) {
                $this->responseJson(502, 'ID参数错误');
            }
            $data = $this->godApply->scalar($id);
        } else {
            $data = $this->godApply->fetchAll();
        }
        if (!empty($data)) {
            $this->responseJson(200, '数据获取成功', $data);
        } else {
            $this->responseJson(502, '数据获取失败');
        }

    }

    /**
     * 数据提交
     */
    public function index_post()
    {
        $post = $this->input->post();
        $this->validPost($post);
        //验证码校验
        $mobile = $post['mobile'];
        $key = "LAST_SMSCODE_{$mobile}";
        $redis_code = $this->cache->redis->get($key);
        if (!$redis_code) $this->responseToJson(502, '验证码已过期');
        if ($redis_code != $post['code']) $this->responseToJson(502, '验证码错误');
        //验证
        $user_data = $this->User_Model->getUserById($this->user_id);
        if ($user_data['is_god'] == 2)
            $this->responseToJson(502, '你已经是大神身份');
        //获取当前登陆人信息
        $post['user_id'] = $this->user_id;
        $post['create_time'] = date("Y-m-d H:i:s", time());
        //更新用户表
        $result_1 = $this->User_Model->update(['id' => $this->user_id],
            ['mobile' => $mobile, 'weixin_url' => $post['weixin_url'], 'update_time' => date('Y-m-d H:i:s')]);
        //插入大神申请表
        unset($post['token'],$post['mobile'],$post['code'],$post['weixin_url']);
        $result_2 = $this->godApply->submitGodApply($post);
        if ($result_1 && $result_2) {
            $this->responseToJson(200, '提交成功,等待后续处理结果');
        } else {
            $this->responseToJson(502, '提交失败');
        }
    }

    /**
     * 数据验证
     * @param $data 需验证的数据数组
     */
    private function validPost($data)
    {
        $require = [
            'mobile' => '手机号',
            'code' => '手机验证码',
            'weixin_url' => '用户微信二维码',
            'game_type' => '游戏类型', //游戏类型  1=>王者荣耀   2=>英雄联盟
            'game_level_id' => '段位id',
            'level_url' => '段位截图',
            'can_zone' => '可接大区',//可接大区   1=>不限  2=>微信区   3=>QQ区
            'can_device' => '可接设备系统', //可接设备系统  1=>不限  2=>IOS  3=>安卓
            'card_url' => '身份证正面照'
        ];
        foreach ($require as $key => $val) {
            // 非空验证
            if (!isset($data[$key]) || empty($data[$key])) {
                $this->responseJson(401, $val . ' 不能为空');
                break;
            }
        }
    }
}
