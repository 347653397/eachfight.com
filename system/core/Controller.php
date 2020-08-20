<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link    https://codeigniter.com
 * @since    Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Libraries
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller
{

    /**
     * Reference to the CI singleton
     *
     * @var    object
     */
    private static $instance;
    protected $user_id;

    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct()
    {
        self::$instance =& $this;

        // Assign all the class objects that were instantiated by the
        // bootstrap file (CodeIgniter.php) to local class variables
        // so that CI can run as one big super object.
        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }

        $this->load =& load_class('Loader', 'core');
        $this->load->initialize();
        log_message('info', 'Controller Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Get the CI singleton
     *
     * @static
     * @return    object
     */
    public static function &get_instance()
    {
        return self::$instance;
    }

    /**
     * 格式化返回数据
     * @param $status 200->成功 502->失败
     * @param $msg
     * @param array $data
     */
    protected function responseToJson(int $status, string $msg, $data = [])
    {
        if (!in_array($status, [200, 502, 501])) {
            echo json_encode(['status' => 402, 'msg' => '返回状态码不正确，请联系开发者!']);
        } else {
            echo json_encode(['status' => $status, 'msg' => $msg, 'data' => $data]);
        }

        exit;
    }

    /**
     * 根据token获取用户id并验证
     */
    protected function getUserId()
    {
        //特殊处理
        $admin_sign = $this->input->post('admin_sign');
        if ($admin_sign) {
            $user_id = $this->input->post('user_id');
            $sign = verify(['order_id' => $this->input->post('order_id'),
                'user_id' => $user_id]);
            if ($admin_sign == $sign) {
                log_message('info', '脚本正常请求参数:' . json_encode($this->input->post()));
                return $user_id;
            } else {
                log_message('info', '异常请求:' . json_encode($this->input->post()));
            }
        }

        $token = $this->input->get_post('token', true);
        log_message('info', 'getUserId获取到的数据token:' . $token);
        if (!$token) $this->responseToJson(502, 'token参数缺少');

        //上线开启
        if (!$this->cache->redis->get($token))
            $this->responseToJson(501, 'token过期');

        $userInfo = $this->User_Model->getUserbyToken($token);
        if (!$userInfo || !isset($userInfo['openid']) || !isset($userInfo['id']))
            $this->responseToJson(501, '该用户还未注册');

        //上线开启
        if ($this->cache->redis->get($token) != md5($userInfo['openid'] . 'eachfight'))
            $this->responseToJson(502, 'token验证未通过');

        return $userInfo['id'];
    }

}
