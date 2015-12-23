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
|	http://codeigniter.com/user_guide/general/routing.html
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
$route['default_controller'] = 'index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['install/touchinstallfile'] = 'Index/write_install_file';
$route['install/checkinstall'] = 'Index/check_install';
$route['install/check'] = 'Index/check';
$route['install/addconfig'] = 'Index/add_database_config_file';
$route['install/addtable'] = 'Index/add_table';
$route['install/adddefaultdata'] = 'Index/add_default_data';

/*WebAPI相关路由*/
$route['web/user']           =   'webApi/User/index';                 //组用户列表
$route['web/user/add']       =   'webApi/User/add';                   //添加用户信息
$route['web/user/edit']      =   'webApi/User/edit';                  //编辑用户信息
$route['web/user/delete']    =   'webApi/User/delete';                //删除用户信息
$route['web/user/search']    =   'webApi/User/search';                //搜索用户
$route['web/user/move']      =   'webApi/User/move_to_anthor_group';  //移动用户到其它组
$route['web/user/admin']     =   'webApi/User/get_admin_user';        //获取管理员用户
$route['web/user/savesess']  =   'webApi/User/save_session';          //保存用户session信息
$route['web/user/destroysess']  =   'webApi/User/destory_session';
$route['web/user/import']    =   'webApi/User/import_user';                //导入用户

$route['web/power']     =   'webApi/Power/index';                //权限列表
$route['web/power/add']      =   'webApi/Power/add';                  //添加权限
$route['web/power/updatestatus']     =   'webApi/Power/power_switch';         //权限开关
$route['web/power/delete']   =   'webApi/Power/delete';               //删除权限
$route['web/power/search']   =   'webApi/Power/search';               //搜索权限
$route['web/power/edit']     =   'webApi/Power/edit';                 //编辑权限
$route['web/power/regenkey'] =   'webApi/Power/regen_auth_key';       //重新生成权限key

$route['web/group']          =   'webApi/Group/index';                //用户组列表
$route['web/group/search']   =   'webApi/Group/search';               //用户组搜索
$route['web/group/add']      =   'webApi/Group/add';                  //添加用户组
$route['web/group/edit']     =   'webApi/Group/edit';                 //修改组名称
$route['web/group/delete']   =   'webApi/Group/delete';               //删除组
$route['web/group/getpower'] =   'webApi/Group/get_power';            //组内权限集
$route['web/group/setpower'] =   'webApi/Group/set_power';            //设置组权限集

$route['web/stats/device']   =   'webApi/Statistics/device_distribution';    //统计设备分布
$route['web/stats/auth']     =   'webApi/Statistics/auth_statistics';        //验证信息统计
$route['web/stats/date']     =   'webApi/Statistics/statistics_by_date';      //时间段内统计信息
$route['web/stats/detail']   =   'webApi/Statistics/statistics_info';        //统计详情

$route['web/log/auth']       =   'webApi/Log/auth_log';       //验证日志
$route['web/log/op']         =   'webApi/Log/op_log';         //操作日志

$route['web/company/info']   =   'webApi/Company/index';      //企业信息
$route['web/company/set']    =   'webApi/Company/set';        //配置企业信息
$route['web/company/upload'] =   'webApi/Company/upload';    //配置企业logo

$route['web/setting/check']  =   'webApi/Setting/check_service';     //获取配置
$route['web/setting/set']    =   'webApi/Setting/set_service';       //添加配置
$route['web/setting/getauthqrcode']   =   'webApi/Setting/get_auth_qrcode';      //获取认证二维码
$route['web/setting/getresult'] = 'webApi/Setting/get_event_result'; //获取事件结果
$route['web/upgrade/check']  =   'webApi/Upgrade/check';      //升级检测
$route['web/upgrade/download'] =   'webApi/Upgrade/download';    //开始升级
$route['web/upgrade/update']  = 'webApi/Upgrade/update';  //更新文件

$route['web/version/info'] = 'webApi/Version/get_version_info'; //版本信息

/*accessApi接口*/
$route['access/qrcode_for_auth'] = 'accessApi/Auth/qrcode_for_auth'; //获取登录二维码
$route['access/event_qrcode_result'] = 'accessApi/Auth/event_qrcode_result'; //获取登录二维码扫描结果
$route['access/realtime_authorization'] = 'accessApi/Auth/realtime_authorization'; //认证推送
$route['access/event_result'] = 'accessApi/Auth/event_result'; //获取事件结果
