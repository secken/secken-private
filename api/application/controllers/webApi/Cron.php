<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Statistics extends API_Controller{

    public function __construct(){
        parent::__construct();
    }

     /**
      * 验证信息统计
      * @return json
      */
     public function auth_statistics(){

         $this->load->model('webModel/Auth_log','auth_log');
         $this->load->model('webModel/Auth_statistics', 'auth_statistics');
         $stats_date_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
         $stats_date_end = date('Y-m-d 23:59:59', strtotime('-1 day'));

         foreach(array(1,2,3,4) as $auth_type){
             $where = array();
             $where = array(
                 'auth_type' => $auth_type,
                 'auth_time >=' => $stats_date_start,
                 'auth_time <=' => $stats_date_end
             );

             $auth_log = $this->auth_log->get_list($where);
             if(!empty($auth_log)){
                 $auth_data = array();

                 foreach($auth_log as $auth){
                    $auth_data = array(
                        'power_id' => $auth['power_id'],
                        'statistics_time' => date('Y-m-d H:i:s')
                    );

                    switch($auth_type){
                        case 1:
                            $auth_data['click_day_auth_count'] = count($auth_log);
                        break;
                        case 2:
                            $auth_data['hand_day_auth_count'] = count($auth_log);
                        break;
                        case 3:
                            $auth_data['face_day_auth_count'] = count($auth_log);
                        break;
                        case 4:
                            $auth_data['noice_day_auth_count'] = count($auth_log);
                        break;
                    }

                    $this->auth_statistics->insert($auth_data);
                 }
             }

        }
     }

     /**
      * 统计详情
      * @param  int $power_id  权限ID
      * @return json
      */
     public function statistics_info(){
         $power_id = $this->input->post('power_id');

         $this->load->model('webModel/Auth_statistics','auth_statistics');
         $list = $this->auth_statistics->get_by_power($power_id);

         if(empty($list)){
             $this->to_api_message(1, 'get_auth_statistics_success', $list);
         }

         $data = array();
         foreach($list as $auth){
             $temp = array();
             $temp = array(

             );
         }
     }

}
