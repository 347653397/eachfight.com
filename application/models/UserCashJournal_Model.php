<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * UserCashJournal_Model Class
 *
 * 用户资金账本Model
 * @category    Models
 */
class UserCashJournal_Model extends MY_Model
{

    const TBL = 'user_cash_journal';

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