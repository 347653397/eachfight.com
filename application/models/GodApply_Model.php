<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 认证大神操作Model
 * @category	Models
 * @author		fengchen <fengchenorz@gmail.com>
 */
class GodApply_Model extends MY_Model {

	const TBL = 'god_apply';
	
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
     * 提交大神申请信息
     * @param $data
     * @return array 新增的申请数据
     */
    public function submitGodApply($data){

        $id = $this->insert($data, true);
        $data = [];
        if($id)
        {
            $data = $this->scalar($id);
        }
        return $data;
    }



}