<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 验证
 */
class Auth extends API_Controller{

    private $power_id = '';
    private $signature = '';

    public function __construct(){
        parent::__construct();

        $this->session_id = time() . uniqid();

        $this->power_id = $this->input->post('power_id', TRUE);
        $this->signature = $this->input->post('signature', TRUE);

        log_message('debug', sprintf("%s---power_id:%s,signature:%s", $this->session_id,$this->power_id, $this->signature));
        //检查签名
        $check = $this->check_power_key();
        if($check === FALSE){
            log_message('ERROR', sprintf("%s--signature_is_error", $this->session_id));
            $this->to_api_message(902, 'signature_is_error', null, false);
        }

        //加载授权类
        $this->load->model('accessModel/Setting','setting');
        $setting = $this->setting->get();

        if(empty($setting)|| empty($setting['app_id']) || empty($setting['app_key'])){
            log_message('ERROR', sprintf("%s--app_info empty", $this->session_id));
            $this->to_api_message(903, 'activate_your_app', null, false);
        }

        $app_info = array();
        $app_info = array(
            'app_id' => $setting['app_id'],
            'app_key' => $setting['app_key'],
            'use_private' => $setting['service_type'] == 2 ? TRUE : FALSE
        );

        $this->load->library('Secken', $app_info);
    }

    /**
     * 检查签名
     * @param  String  $power_id  资源ID
     * @param  String  $signature 签名
     * @return bool
     */
    private function check_power_key(){

        $this->load->model('accessModel/Power_model','power');
        $power = $this->power->get($this->power_id);
        if(empty($power)){
            log_message('debug', sprintf("%s---power_id:%s,signature:%s, power_id is not exists", $this->session_id,$this->power_id, $this->signature));
            return FALSE;
        }

        //组织签名数据
        $post = $this->input->post();
        unset($post['signature']);
        if(isset($post['callback'])){
            $post['callback'] = urlencode($post['callback']);
        }

        if(isset($post['username'])){
            $post['username'] = urlencode($post['username']);
        }

        //比对签名
        $this->load->helper('signature');
        $t_signature = get_signature($post, $power['power_key']);
        //var_dump($t_signature);
        log_message('debug', sprintf("%s--signature:%s,t_signature:%s", $this->session_id, $this->signature, $t_signature));
        $cmp_resut = strcmp($this->signature, $t_signature);
        if($cmp_resut == 0){
            return TRUE;
        }else{
            log_message('ERROR', sprintf("%s--signature:%s,t_signature:%s", $this->session_id, $this->signature, $t_signature));
            return FALSE;
        }
    }

    /**
     * 扫码登录
     * @param string $username          企业下员工用户名
     * @param Int    $auth_type(可选)   验证类型1(确定，默认)，3(人脸)，4(声纹)
     * @param string $callback (可选)   回调地址
     * @return json
     *
     */
    public function qrcode_for_auth(){

        $auth_type = $this->input->post('auth_type', TRUE);
        $callback = $this->input->post('callback',TRUE);

        $auth_type = $auth_type ? intval($auth_type) : 1;
        $callback = $callback ? urldecode($callback) : '';

        $auth = $this->secken->getAuth($auth_type, $callback);
        $auth['description'] = $this->secken->getMessage();

        $code = $this->secken->getCode();
        log_message('DEBUG', sprintf("%s--get qrcode,code:%s", $this->session_id, $code));

        log_message('DEBUG', sprintf("%s--
        _result:%s", $this->session_id, json_encode($auth)));

        echo json_encode($auth);
    }

    /**
     * 主动推送认证请求
     * @param string $username         企业下员工用户名
     * @param Int    $auth_type(可选)   验证类型（1: 点击确认按钮  3: 人脸验证 4: 声音验证）
     * @param Int    $action_type(可选) 操作类型(1:登录验证，2:请求验证，3:交易验证，4:其它验证)
     * @param string $callback (可选)   回调地址
     * @return json
     */
    public function realtime_authorization(){
        $username = $this->input->post('username', TRUE);
        $auth_type = $this->input->post('auth_type', TRUE);
        $action_type = $this->input->post('action_type', TRUE);
        $callback = $this->input->post('callback', TRUE);

        $auth_type = $auth_type ? intval($auth_type) : 1;
        $action_type = $action_type ? intval($action_type) : 1;
        $callback = $callback ? urldecode($callback) : '';

        $this->load->model('accessModel/User_model','user');
        $user_info = $this->user->get_yangcong_uid($username);

        if(!empty($user_info) && !empty($user_info['yangcong_uid'])){
            $realtime = $this->secken->realtimeAuth($user_info['yangcong_uid'], $auth_type, $action_type, $callback);
        }else{
            $realtime = $this->secken->realtimeAuth(0, $auth_type, $action_type, $callback, '', $username);
        }

        $realtime['description'] = $this->secken->getMessage();

        $code = $this->secken->getCode();
        if($code == 200){
            //添加用户事件
            $this->add_event($username,$realtime['event_id']);
            log_message('DEBUG', sprintf("%s--username:%s, event_id:%s", $this->session_id, $username, $realtime['event_id']));
            $this->add_auth_log($username, $auth_type, $this->power_id, $realtime['event_id']);
        }else{
            log_message('DEBUG', sprintf("%s--username:%s, realtime_authorization_result:%s", $this->session_id, $username, json_encode($realtime)));
        }

        echo json_encode($realtime);
    }

    /**
     * 获取事件结果
     * @param  string event_id  事件ID
     * @return json
     */
    public function event_result(){
        $event_id = $this->input->post('event_id', TRUE);

        $result = $this->secken->getResult($event_id);
        $result['description'] = $this->secken->getMessage();
        $respone_code = $this->secken->getCode();
        log_message('DEBUG', sprintf("%s--event_id:%s", $this->session_id,$event_id));
        if($respone_code == 200){
            $this->load->model('accessModel/User_event','user_event');
            $get = $this->user_event->get($event_id);
            if(empty($get)){
                log_message('ERROR', sprintf("%s--status:%s", $this->session_id, 901));
                $result['status'] = 604;
                $result['description'] = $this->lang->line('901');
            }

            //检测权限
            $check = $this->check_power($get['power_id'], $get['user_name']);
            if($check === FALSE){
                log_message('ERROR', sprintf("%s--status:%s", $this->session_id, 900));
                $result['status'] = 900;
                $result['description'] = $this->lang->line('900');
            }

            //添加验证结果
            $this->load->model('accessModel/Auth_log','auth_log');
            $this->auth_log->update_event_result($event_id, 1);
        }else{
            log_message('DEBUG', sprintf("%s--event_result:%s", $this->session_id, json_encode($result)));
        }

        echo json_encode($result);
    }

    /**
     * 获取二维码事件结果
     * @param  string event_id  事件ID
     * @return json
     */
    public function event_qrcode_result(){

        $event_id = $this->input->post('event_id', TRUE);
        $result = $this->secken->getResult($event_id);
        $respone_code = $this->secken->getCode();
        if($respone_code == 200){
            $this->load->model('accessModel/User_model','user');
            $user_info = $this->user->get_username_by_yangcong_uid($result['uid']);
            $this->load->model('accessModel/Power_model','power');
            $id = $this->power->get_by_power_id($this->power_id);

            $check = $this->check_power($id["id"], $user_info["user_name"]);
            if($check === FALSE){
                log_message('ERROR', sprintf("%s--%s wang to access by scan qrcode, but status:%s", $this->session_id, $user_info["user_name"], 900));
                $new_result['status'] = 900;
                $new_result['description'] = $this->lang->line('900');
                $result = $new_result;
            }
            //添加验证结果
            $this->load->model('accessModel/Auth_log','auth_log');
            $this->auth_log->update_event_result($event_id, 1);
        } else {
            log_message('DEBUG', sprintf("%s--event_result:%s", $this->session_id, json_encode($result)));
        }
        echo json_encode($result);
    }

    /**
     * 检查权限
     * @param string $power_id  针对不同资源分配的power_id
     * @param string $username  进行检测权限的用户名
     * @return bool
     */
    private function check_power($power_id, $username){

        //获取用户当前的组
        $this->load->model('accessModel/User_model','user');
        $group_ids = $this->user->get_user_group($username);

        if(empty($group_ids)){
            return FALSE;
        }

        $gids = array();
        foreach($group_ids as $group){
            $gids[] = $group['gid'];
        }

        //查询组对应的权限集
        $this->load->model('accessModel/Group_power','group_power');
        $power_ids = $this->group_power->get($gids);
        if(empty($power_ids)){
            return FALSE;
        }

        $pids = array();
        foreach($power_ids as $power){
            $pids[] = $power['id'];
        }

        if(in_array($power_id, $pids)){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * 添加用户验证事件
     * @param string $username  验证用户名
     * @param string $event_id  验证事件id
     * @return bool
     */
    private function add_event($username, $event_id){

        $this->load->model('accessModel/Power_model', 'power');
        $power = $this->power->get_by_power_id($this->power_id);
        $this->load->model('accessModel/User_event','user_event');
        $insertData = array();
        $insertData = array(
            'user_name' => $username,
            'power_id' => $power['id'],
            'event_id' => $event_id
        );
        $insert_id = $this->user_event->insert($insertData);
        if($insert_id){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}
