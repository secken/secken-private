<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class API_Controller extends CI_Controller
{
    private $jsonp_callback='';

    public function __construct()
    {
        parent::__construct();

        //加载语言文件
        $this->lang->load('api');

        $this->jsonp_callback = $this->input->get('secken_jsonp_callback');

        $controller_name = $this->uri->rsegment(1);
        $controller_name = strtolower($controller_name);

        // $method_name = $this->uri->rsegment(2);
        // $method_name = strtolower($method_name);
        // echo $method_name;
        $no_need_login_controller_method = array();
        $no_need_login_controller_method = array(
            'index','auth','user','setting'
        );

        if(!in_array($controller_name, $no_need_login_controller_method) && !$this->is_login()){
            $this->to_api_message(10000, 'need_login_for_access');
        }

    }

    /**
     * @return Bool
     */
    public function is_login(){
        return $this->session->has_userdata('user_id');
    }

    public function get_user_info($session_name = ''){
        if(empty($session_name)){
            return $this->session->userdata();
        }else{
            return $this->session->userdata($session_name);
        }
    }

    /**
     * 添加操作日志
     *
     */
    protected function add_op_log($op_description, $op_result){

        //获取操作者
        $op_username = $this->get_user_info('true_name');
        //获取操作类型
        $controller = $this->uri->rsegment(1);
        $controller = strtolower($controller);
        $method = $this->uri->rsegment(2);
        $method = strtolower($method);

        $line_name = sprintf("%s_%s",$controller, $method);
        $op_name = $this->lang->line($line_name);

        $this->load->model('webModel/Op_log','op_log');
        $insertData = array();
        $insertData = array(
            'op_user' => $op_username,
            'op_name' => $op_name,
            'op_intro' => $op_description,
            'op_status' => $op_result,
            'op_time' => date('Y-m-d H:i:s')
        );

        $this->op_log->insert($insertData);

    }

    public function add_auth_log($auth_user, $auth_type, $power_id, $event_id){
        $this->load->model('accessModel/Power_model','power');
        $power = $this->power->get($power_id);
        $power_name = !empty($power) ? $power['name'] : '';

        $insertData = array();
        $insertData = array(
            'auth_user' => $auth_user,
            'auth_type' => $auth_type,
            'power_name' => $power_name,
            'event_id' => $event_id,
            'power_id' => $power['id'],
            'auth_time' => date('Y-m-d H:i:s')
        );

        $this->load->model('webModel/Auth_log', 'auth_log');
        $this->auth_log->insert($insertData);
    }

    protected function to_api_message($status, $message, $extra_data=null, $is_jsonp = TRUE, $count = null){
        $data = array();
        $data = array(
            'status' => $status,
            'description' => ''
        );

        $line_message = $this->lang->line($message);
        if($line_message){
            $data['description'] = $line_message;
        }else{
            $data['description'] = $message;
        }

        if($status == 1 && is_array($extra_data)){
            $data['data'] = $extra_data;
        }

        if(!is_null($count)){
            $data['count'] = $count;
        }

        if($is_jsonp){
            echo $this->jsonp_callback.'('.json_encode($data).')';
        }else{
            echo json_encode($data);
        }
        die();
    }
}
