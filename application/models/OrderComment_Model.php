<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 陪练订单评论Model
 * @category    Models
 * @author      guochao
 */
class OrderComment_Model extends MY_Model
{

    const TBL = 'order_comment';

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
     * 获取所有星星总数
     * @param $where
     * @return mixed
     */
    public function getAllStar($where)
    {
        $this->db->select_sum('star_num', 'all_star');
        $this->db->where($where);
        $query = $this->db->get(self::TBL)->row();

        return $query;
    }
}