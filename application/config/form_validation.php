<?php
/**
 * Created by PhpStorm.
 * User: guochao
 * Date: 2017/11/14
 * Time: 22:54
 */

$config = array(
    //下单参数验证
    'create_order' => [
        array(
            'field' => 'game_type',
            'label' => 'game_type', //游戏类型 1=>王者荣耀   2=>英雄联盟
            'rules' => 'integer|required|in_list[1,2]',
            'errors' => array(
                'required' => 'game_type参数错误'
            ),
        ),
        array(
            'field' => 'game_mode',
            'label' => 'game_mode', //游戏模式 1=>排位赛   2=>其他
            'rules' => 'integer|required|in_list[1,2]',
            'errors' => array(
                'required' => 'game_mode参数错误'
            ),
        ),
        array(
            'field' => 'is_except',
            'label' => 'is_except',  //是否计胜负   1=>计   2=>不计
            'rules' => 'integer|required|in_list[1,2]',
            'errors' => array(
                'required' => 'is_except参数错误'
            ),
        ),
        array(
            'field' => 'device',
            'label' => 'device', //设备系统  1=>未知  2=>IOS  3=>安卓
            'rules' => 'integer|required|in_list[1,2,3]',
            'errors' => array(
                'required' => 'device参数错误'
            ),
        ),
        array(
            'field' => 'game_zone',
            'label' => '大区', //大区   1=>未知  2=>微信区   3=>QQ区
            'rules' => 'integer|required|in_list[1,2,3]',
            'errors' => array(
                'required' => 'game_zone参数错误'
            ),
        ),
        array(
            'field' => 'game_level_id',
            'label' => 'game_level_id',
            'rules' => 'integer|required',
            'errors' => array(
                'required' => 'game_level_id参数错误'
            ),
        ),
        array(
            'field' => 'game_num',
            'label' => 'game_num',
            'rules' => 'integer|required|in_list[1,2,3]',
            'errors' => array(
                'required' => 'game_num参数错误'
            ),
        ),
        array(
            'field' => 'remark',
            'label' => 'remark',  //下单备注(游戏id等)
            'rules' => 'trim|max_length[10]'
        )],

    //用户操作订单
    'operate_order' => [
        array(
            'field' => 'order_id',
            'label' => 'order_id',
            'rules' => 'integer|required',
            'errors' => array(
                'required' => '订单id参数错误'
            ),
        ),
        array(
            'field' => 'type',
            'label' => 'type',
            'rules' => 'integer|required|in_list[1,2,3,4]',
            'errors' => array(
                'required' => '操作类型参数错误'
            ),
        ),
    ],

    //提交评论
    'submit_comment' => [
        array(
            'field' => 'order_id',
            'label' => 'order_id',
            'rules' => 'integer|required',
            'errors' => array(
                'required' => 'order_id参数错误'
            ),
        ),
        array(
            'field' => 'star_num',
            'label' => 'star_num',
            'rules' => 'integer|required|in_list[1,2,3,4,5]',
            'errors' => array(
                'required' => 'star_num参数错误'
            ),
        ),
        array(
            'field' => 'context',
            'label' => 'context',
            'rules' => 'trim|max_length[200]',
            'errors' => array(
                'required' => 'context参数错误'
            ),
        ),
    ],

    //提交申诉
    'submit_order_shensu' => [
        array(
            'field' => 'order_id',
            'label' => 'order_id',
            'rules' => 'integer|required',
            'errors' => array(
                'required' => 'order_id参数错误'
            ),
        ),
        array(
            'field' => 'victory_num',
            'label' => 'victory_num',
            'rules' => 'integer|required|in_list[0,1,2,3]',
            'errors' => array(
                'required' => 'victory_num参数错误'
            ),
        ),
        array(
            'field' => 'record_url',
            'label' => 'record_url',
            'rules' => 'trim|required|max_length[255]',
            'errors' => array(
                'required' => 'record_url参数错误'
            ),
        ),
        array(
            'field' => 'shensu_des',
            'label' => 'shensu_des',
            'rules' => 'trim|max_length[200]',
            'errors' => array(
                'required' => 'shensu_des参数错误'
            ),
        ),
    ]


);