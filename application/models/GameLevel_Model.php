<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GameLevel_Model Model Class
 *
 * 用户Model
 * @category    Models
 * @author        guochao
 */
class GameLevel_Model extends MY_Model
{

    const TBL = 'game_level';

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
     * 获取段位价格配置
     */
    public function getGameLevel(int $game_type)
    {
        $this->db->select(['id', 'game_level', 'one_price'])
            ->from(self::TBL)
            ->where("game_type", $game_type)
            ->order_by('order', 'asc');
        $query = $this->db->get();
        $data = $query->result_array();
        $query->free_result();
        return $data;
    }

    /**
     * 根据id获取段位名称
     */
    public function getGameLevelName(int $id)
    {
        return $this->db->select(['game_level'])
            ->from(self::TBL)
            ->where("id", $id)
            ->order_by('create_time', 'desc')
            ->get()
            ->row();
    }

}