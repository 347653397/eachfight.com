<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('GameLevel_Model');
    }

    public function index()
    {
        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);


        $proArr = array(
            array('id' => 1, 'name' => '特等奖', 'v' => 1),
            array('id' => 2, 'name' => '一等奖', 'v' => 5),
            array('id' => 3, 'name' => '二等奖', 'v' => 10),
            array('id' => 4, 'name' => '三等奖', 'v' => 12),
            array('id' => 5, 'name' => '四等奖', 'v' => 22),
            array('id' => 6, 'name' => '没中奖', 'v' => 50)
        );

        for ($i = 1; $i <=100; $i++) {
            $arr[] = $this->get_rand($proArr);
        }
        foreach ($arr as $val){
            if($val['id'] == 6 ){
                $arr_new[]  = $val;
            }
        }
        dump(count($arr_new));
    }


    private function get_rand($proArr)
    {
        $result = array();
        foreach ($proArr as $key => $val) {
            $arr[$key] = $val['v'];
        }
        // 概率数组的总概率
        $proSum = array_sum($arr);
        asort($arr);
        // 概率数组循环
        foreach ($arr as $k => $v) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $v) {
                $result = $proArr[$k];
                break;
            } else {
                $proSum -= $v;
            }
        }
        return $result;
    }
}
