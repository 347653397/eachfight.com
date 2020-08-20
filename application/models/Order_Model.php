<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 认证大神操作Model
 * @category    Models
 * @author      fengchen <fengchenorz@gmail.com>
 */
class Order_Model extends MY_Model
{

    const TBL = 'order';

    /**
     * 主键：{"id"}
     *
     * @access private
     */
    private $_unique_key = array('id');

    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    function __construct()
    {
        parent::__construct();

    }

    /**
     * 根据用户id获取用户当前的订单
     * @param int $user_id
     * @return mixed
     */
    public function getUserOrder(int $user_id)
    {
        return $this->db->select('*')
            ->from(self::TBL)
            ->where("user_id", $user_id)
            ->order_by('create_time', 'desc')
            ->get()
            ->row();
    }

    /**
     * 根据大神用户id获取大神当前的订单
     * @param int $god_user_id
     * @return mixed
     */
    public function getGodOrder(int $god_user_id)
    {
        return $this->db->select('*')
            ->from(self::TBL)
            ->where("god_user_id", $god_user_id)
            ->order_by('create_time', 'desc')
            ->get()
            ->row();
    }
}