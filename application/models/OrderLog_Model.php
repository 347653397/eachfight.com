<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 订单变更日志记录
 * @category    Models
 * @author      fengchen <fengchenorz@gmail.com>
 */
class OrderLog_Model extends MY_Model
{

    const TBL = 'order_log';

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