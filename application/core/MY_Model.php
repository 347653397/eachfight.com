<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 通用 model
 * @category	Models
 * @author		fengchen <fengchenorz@gmail.com>
 */
class MY_Model extends CI_Model {

	const TBL = "";
	
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

        $this->load->database();
    }

    /**
     * 执行sql
     * @param $sql
     * @param bool $affect_num 是否返回影响行数
     * @return mixed
     */
    function query($sql, $affect_num=false){

        $query = $this->db->query($sql);

        if($affect_num){

            $query = $this->db->affected_rows();
        }
        return $query;
    }

    /**
     * 获取单条
     * @access public
	 * @param int $id 主键ID
     * @return array - 单条信息
     */
	public function scalar($id)
	{
        $table = self::TBL;

        if(!$table){

            $model =  get_called_class();

            $table = $model::TBL;
        }

		$data = array();
		
		$this->db->select('*')->from($table)->where('id', $id)->limit(1);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			$data = $query->row_array();
		}
		$query->free_result();

		return $data;
	}
    /**
     * 获取单条
     * @access public
     * @param array $condition 条件数组
     * @return array - 单条信息
     */
    public function scalarBy($condition)
    {
        $table = self::TBL;

        if(!$table){

            $model =  get_called_class();

            $table = $model::TBL;
        }

        $data = array();

        $this->db->select('*')->from($table)->where($condition)->limit(1);

        $query = $this->db->get();

        if($query->num_rows() == 1)
        {
            $data = $query->row_array();
        }
        $query->free_result();

        return $data;
    }
    /**
     * 获取多条
     *
     * @access public
     * @param $where where (e.g. array('field' =>'value',...))
     * @return array - 多条
     */
    public function fetchAll( $where = [] )
    {
        $table = self::TBL;

        if(!$table){

            $model =  get_called_class();

            $table = $model::TBL;
        }

        $this->db->where($where);

        $query = $this->db->get($table);

        $data = $query->result_array();

        $query->free_result();

        return $data;
    }
    /**
     * 插入数据
     * @param $data 插入的数据array
     * @param bool $return 是否需要返回插入成功的id
     * @return bool
     */
    function insert($data, $return = false){

        $table = self::TBL;

        if(!$table){

            $model =  get_called_class();

            $table = $model::TBL;
        }
        $query = $this->db->insert($table, $data);

        if($return){

            $query = $this->db->insert_id();
        }
        return $query;
    }

    /**
     * 更新数据
     * @param $where where (e.g. array('field' =>'value',...))
     * @param $data $data (e.g. array('field' =>'value',...))
     * @param int $limit
     * @return bool
     */
    function update($where, $data, $limit=1){

        $table = self::TBL;

        if(!$table){
            $model =  get_called_class();

            $table = $model::TBL;
        }
        $this->db->where($where);

        $this->db->limit($limit);

        $this->db->update($table, $data);

        return $this->db->affected_rows();
    }

}