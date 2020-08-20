<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * OrderShensu_Model Model Class
 *
 * 订单申诉
 * @category    Models
 * @author      fengchen <fengchenorz@gmail.com>
 */
class OrderShensu_Model extends MY_Model
{

    const TBL = 'order_shensu';

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


}