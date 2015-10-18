<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Cron extends API_Controller{

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

                 $statis = array();
                 foreach($auth_log as $auth){

                    if(isset($statis[$auth['power_id']])){
                        switch($auth_type){
                            case 1:
                                $statis[$auth['power_id']]['click_day_auth_count'] += 1;
                            break;
                            case 2:
                                $statis[$auth['power_id']]['hand_day_auth_count'] += 1;
                            break;
                            case 3:
                                $statis[$auth['power_id']]['face_day_auth_count'] += 1;
                            break;
                            case 4:
                                $statis[$auth['power_id']]['noice_day_auth_count'] += 1;
                            break;
                        }
                    }else{
                        $statis[$auth['power_id']] = array();

                        switch($auth_type){
                            case 1:
                                $statis[$auth['power_id']]['click_day_auth_count'] = 1;
                            break;
                            case 2:
                                $statis[$auth['power_id']]['hand_day_auth_count'] = 1;
                            break;
                            case 3:
                                $statis[$auth['power_id']]['face_day_auth_count'] = 1;
                            break;
                            case 4:
                                $statis[$auth['power_id']]['noice_day_auth_count'] = 1;
                            break;
                        }
                    }

                    $statis[$auth['power_id']]['power_id'] = $auth['power_id'];
                    $statis[$auth['power_id']]['statistics_time'] = date('Y-m-d 23:59:59', strtotime('-1 day'));
                 }

                 sort($statis);

                 $this->auth_statistics->insert_batch($statis);
             }
        }
     }
}
