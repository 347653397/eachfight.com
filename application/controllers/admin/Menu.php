<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu extends CI_Controller {
    public function index(){
        $this->load->view('admin/menu/index');
    }
    public function top(){
        $this->load->view('admin/menu/top');
    }
    public function left(){
        $this->load->view('admin/menu/left');
    }
    public function main(){
        $data['info'] = $_SERVER;
        // dump($info);exit;
        $this->load->view('admin/menu/main',$data);
    }
}