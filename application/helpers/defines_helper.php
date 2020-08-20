<?php defined('BASEPATH') || exit('No direct script access allowed');

/**************用户相关**********************/
//用户性别
if (!function_exists('user_gender')) {
    function user_gender()
    {
        return array(1 => '男', 2 => '女', 0 => '未知');
    }
}

//是否大神
if (!function_exists('is_god')) {
    function is_god()
    {
        return array(1 => '不是', 2 => '是');
    }
}

//用户状态
if (!function_exists('user_status')) {
    function user_status()
    {
        return array(1 => '正常', 2 => '冻结');
    }
}

/**************大神相关**********************/
//大神可接大区
if (!function_exists('can_zone')) {
    function can_zone()
    {
        return array(1 => '不限制', 2 => '微信区', 3 => 'QQ区');
    }
}

//大神可接设备系统
if (!function_exists('can_device')) {
    function can_device()
    {
        return array(1 => '不限制', 2 => 'IOS', 3 => '安卓');
    }
}

//大神接单状态
if (!function_exists('god_status')) {
    function god_status()
    {
        return array(1 => '正常', '锁定');
    }
}

//大神申请审核状态
if (!function_exists('god_apply_status')) {
    function god_apply_status()
    {
        return array(1 => '待审 ', 2 => '通过', 3 => '拒绝');
    }
}

/**************订单相关**********************/
//游戏类型
if (!function_exists('game_type')) {
    function game_type()
    {
        return array(1 => '王者荣耀', 2 => '英雄联盟');
    }
}

//游戏模式
if (!function_exists('game_mode')) {
    function game_mode()
    {
        return array(1 => '排位赛', 2 => '其他');
    }
}

//是否计胜负
if (!function_exists('is_except')) {
    function is_except()
    {
        return array(1 => '计输赢', 2 => '不计输赢');
    }
}

//设备系统
if (!function_exists('device')) {
    function device()
    {
        return array(1 => '未知', 2 => 'IOS', 3 => '安卓');
    }
}

//大区
if (!function_exists('game_zone')) {
    function game_zone()
    {
        return array(1 => '未知', 2 => '微信区', 3 => 'QQ区');
    }
}

//申诉状态
if (!function_exists('is_shensu')) {
    function is_shensu()
    {
        return array(0 => '未申诉', 1 => '已申诉');
    }
}

//订单状态
if (!function_exists('order_status')) {
    function order_status()
    {
        return array(
            1 => '用户下单',
            2 => '未接取消',  //用户主动或时间到了自动取消
            3 => '大神抢单',
            4 => '大神已接用户取消',
            5 => '用户准备',
            6 => '完成游戏 ',
            7 => '大神提交战绩',
            8 => '用户发起申诉 ',
            9 => '订单完成'  //胜0局或用户确认战绩或申诉完成
        );
    }
}

//申诉处理状态
if (!function_exists('shensu_status')) {
    function shensu_status()
    {
        return array(1 => '待处理', 2 => '申诉完成');
    }
}

/**************用户资金往来相关**********************/
//交易类型
if (!function_exists('trade_type')) {
    function trade_type()
    {
        return array(1 => '充值', 2 => '订单支付', 3 => '订单收入', 4 => '提现');
    }
}

//流水方向
if (!function_exists('inorout')) {
    function inorout()
    {
        return array(1 => '进', 2 => '出');
    }
}

//支付方式
if (!function_exists('pay_type')) {
    function pay_type()
    {
        return array(1 => '账户余额 ', 2 => '微信');
    }
}

//充值状态
if (!function_exists('recharge_status')) {
    function recharge_status()
    {
        return array(1 => '充值中 ', 2 => '充值完成', 3 => '充值失败');
    }
}

//提现状态
if (!function_exists('withdraw_status')) {
    function withdraw_status()
    {
        return array(1 => '提现中 ', 2 => '提现完成', 3 => '提现失败');
    }
}

?>