<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['admin'] = 'admin/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
// 大神端路由
$route['api/GodBattleRecord/(:num)']['get'] = 'api/god/GodBattleRecord/index_get/$1';//获取一条战绩
$route['api/GodBattleRecord']['get'] = 'api/god/GodBattleRecord/index_get';//获取一条战绩
$route['api/GodBattleRecord']['post'] = 'api/god/GodBattleRecord/index_post';//提交战绩

$route['api/GodApply/(:num)']['get'] = 'api/god/GodApply/index_get/$1';//获取一条大神申请
$route['api/GodApply']['get'] = 'api/god/GodApply/index_get';//获取多条大神申请
$route['api/GodApply']['post'] = 'api/god/GodApply/index_post';//提交大神申请信息

$route['api/QiniuToken']['get'] = 'api/Qiniu/index_get';//获取七牛token
$route['api/GodInfo']['get'] = 'api/god/God/index_get';//获取大神信息
$route['api/GrapOrder']['post'] = 'api/god/GrapOrder/index_post';//获取大神信息
$route['api/GodOrder']['get'] = 'api/god/GrapOrder/index_get';
