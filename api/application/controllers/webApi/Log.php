<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Log extends API_Controller{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 验证日志
     * @param  int $auth_type   验证方式
     * @param  int $auth_result 验证结果
     * @param  int $page        页码
     * @return json
     */
     public function auth_log(){
         $auth_type = $this->input->get_post('auth_type');
         $auth_result = $this->input->get_post('auth_result');
         $page = $this->input->get_post('page');
         $page = $page ? $page : 1;

         $limit = 50;
         $offset = ($page-1)*$limit;

         $where = array();
         if($auth_type){
             $where['auth_type'] = $auth_type;
         }

         if($auth_result != -1){
             $where['auth_result'] = $auth_result;
         }

         $this->load->model('webModel/Auth_log','auth_log');

         $count = $this->auth_log->get_count($where);
         $page_count = ceil($count/$limit);

         $list = $this->auth_log->get_list($where, $limit, $offset);

         if(empty($list)){
             $this->to_api_message(1, 'get_list_success', $list);
         }

         $auth_types = $data = array();
         $auth_types = array(
             1 => '点击按钮验证',
             2 => '手势验证',
             3 => '人脸验证',
             4 => '声音验证'
         );
         $auth_result = array(
             '失败',
             '成功',
             '取消'
         );
         $data = array();
         foreach($list as $auth_log){
             $temp = array();
             $temp = array(
                 'auth_user' => $auth_log['auth_user'],
                 'auth_type' => $auth_types[$auth_log['auth_type']],
                 'power_name' => $auth_log['power_name'],
                 'auth_result' => $auth_result[$auth_log['auth_result']],
                 'auth_time' => $auth_log['auth_time']
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_list_success', $data, true, $page_count);
     }

    /**
     * 操作日志
     * @param  int $page       页码
     * @param  int $op_status
     * @return json
     */
     public function op_log(){

         $op_status = $this->input->get_post('op_status');
         $page = $this->input->get_post('page');
         $page = $page ? $page : 1;

         $limit = 50;
         $offset = ($page-1)*$limit;

         $where = array();
         if($op_status != -1){
             $where['op_status'] = $op_status;
         }

         $this->load->model('webModel/Op_log','op_log');

         $count = $this->op_log->get_count($where);
         $page_count = ceil($count/$limit);
         $list = $this->op_log->get_list($where, $limit, $offset);

         if(empty($list)){
             $this->to_api_message(1, 'get_list_success', $list);
         }

         foreach($list as $op_log){
             $temp = array();
             $temp = array(
                 'op_user' => $op_log['op_user'],
                 'op_name' => $op_log['op_name'],
                 'op_intro' => $op_log['op_intro'],
                 'op_time' => $op_log['op_time'],
                 'op_status' => $op_log['op_status'] == 1? '成功':'失败'
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_list_success', $data, true, $page_count);
     }

}
