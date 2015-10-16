<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 配置
 */
class Setting extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/Setting_model','setting');
    }

    /**
     * 返回配置信息是否已配置
     * @param NULL
     * @return json
     * service   bool  服务是否已填
     * auth_info bool  app 验证信息是否已填
     */
    public function check_service(){

        $get = $this->setting->get();
        $data = array();
        if(empty($get)){
            $this->to_api_message(1, 'get_setting_list_success', array());
        }

        foreach($get as $item => $value){
            $data['app_info'] = 0;
            if(($item == 'app_id' || $item == 'app_key') && !empty($value)){
                $data['app_info'] = 1;
            }elseif($item == 'service_type'){
                $data['service_type'] = empty($value) ? 0 : 1;
            }
        }

        $this->to_api_message(1, 'get_setting_list_success', $data);
    }

    /**
     * 选择服务
     * @param  string $app_id        应用id
     * @param  stirng $app_key       应用密匙
     * @return json
     */
    public function set_service(){
        $app_id = $this->input->get_post('app_id', TRUE);
        $app_key = $this->input->get_post('app_key', TRUE);

        $service_type = $this->check_app_info($app_id, $app_key);

        if($service_type == 0){
            $this->to_api_message(0, 'app_info_error');
        }

        $data = array();
        $data = array(
            'service_type' => $service_type,
            'app_id' => $app_id,
            'app_key' => $app_key
        );

        $affected = 0;
        $get = $this->setting->get();
        if(empty($get)){
            $affected = $this->setting->insert($data);
        }else{
            $where = array();
            $where = array(
                'id' => $get['id']
            );
            $affected = $this->setting->update($data, $where);
        }

        if($affected){
            $this->to_api_message(1, 'set_service_success');
        }else{
            $this->to_api_message(0, 'set_service_failed');
        }
    }

    /**
     * 检查应用信息属于公有云还是私有云
     * @param  string $app_id   应用ID
     * @param  string $app_key  应用密匙
     * @return int 0为验证失败，1为公有云，2为私有云
     */
    private function check_app_info($app_id, $app_key){

        $app_info = array();
        $app_info = array(
            'app_id' => $app_id,
            'app_key' => $app_key
        );

        //检查app_id所属的服务类型
        foreach(array(1,2) as $service_type){

            $use_private = $service_type == 2 ? TRUE : FALSE;

            $app_info['use_private'] = $use_private;
            $this->load->library('Secken', $app_info);
            $this->secken->getAuth();
            $code = $this->secken->getCode();
            if($code == 200){
                return $service_type;
            }elseif($code == 402){
                continue;
            }
        }

        return 0;
    }

    /**
     * 管理员授权
     * @param null
     * @return json
     */
    public function get_auth_qrcode(){

        $this->load->model('webModel/Setting_model','setting');
        $setting = $this->setting->get();
        if(empty($setting)|| empty($setting['app_id']) || empty($setting['app_key'])){
            $this->to_api_message(0, 'activate_your_app');
        }

        $app_info = array();
        $app_info = array(
            'app_id' => $setting['app_id'],
            'app_key' => $setting['app_key'],
            'use_private' => $setting['service_type'] == 2 ? TRUE : FALSE
        );

        $this->load->library('Secken', $app_info);
        $auth_qrcode = $this->secken->getAuth();
        $response_code = $this->secken->getCode();
        $response_message = $this->secken->getMessage();

        if($response_code == 200){
            $data = array();
            $data = array(
                'qrcode' => $auth_qrcode['qrcode_url'],
                'event_id' => $auth_qrcode['event_id']
            );
            $this->to_api_message(1, $response_message, $data);
        }else{
            $this->to_api_message(0, $response_message);
        }
    }

    /**
     * 获取事件结果
     * @param  $event_id  事件id
     * @return json
     */
    public function get_event_result(){

        $event_id = $this->input->get_post('event_id', TRUE);

        $this->load->model('webModel/Setting_model','setting');
        $setting = $this->setting->get();
        if(empty($setting)|| empty($setting['app_id']) || empty($setting['app_key'])){
            $this->to_api_message(0, 'activate_your_app');
        }

        $app_info = array();
        $app_info = array(
            'app_id' => $setting['app_id'],
            'app_key' => $setting['app_key'],
            'use_private' => $setting['service_type'] == 2 ? TRUE : FALSE
        );

        $this->load->library('Secken', $app_info);
        $result = $this->secken->getResult($event_id);
        $response_code = $this->secken->getCode();
        $response_message = $this->secken->getMessage();

        if($response_code == 200){

            $identity_name = $setting['service_type'] == 2 ? $result['username'] : $result['uid'];

            $data = array();
            $data = array(
                'service_type' => $setting['service_type'],
                'identity_name' => $identity_name
            );

            $this->to_api_message(1, $response_message, $data);
        }else{
            $this->to_api_message(0, $response_message);
        }
    }
}
