<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Statistics extends API_Controller{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 设备平台分布
     * @return json
     */
     public function device_distribution(){

         $this->load->model('webModel/device_statistics','device_statistics');
         $list = $this->device_statistics->device_distribution();

         if(empty($list)){
             $this->to_api_message(1, 'get_device_distribution_success', $list);
         }

         $data = array();
         foreach($list as $device){
             $temp = array();
             $temp = array(
                 'device_type' => $device['device_type'],
                 'device_count' => $device['device_count']
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_device_distribution_success', $data);
     }

     /**
      * 验证信息统计
      * @return json
      */
     public function auth_statistics(){

         $this->load->model('webModel/Auth_statistics','auth_statistics');

         $statistics = $this->auth_statistics->get_auth_statistics();

         $data = array();
         $data = array(
             array(
                 'auth_type' => 1,
                 'auth_name' => '普通验证',
                 'sum' => isset($statistics['click_sum']) ? $statistics['click_sum'] : 0
             ),
             array(
                 'auth_type' => 2,
                 'auth_name' => '手势验证',
                 'sum' => isset($statistics['hand_sum']) ? $statistics['hand_sum'] : 0
             ),
             array(
                 'auth_type' => 3,
                 'auth_name' => '人脸验证',
                 'sum' => isset($statistics['face_sum']) ? $statistics['face_sum'] : 0
             ),
             array(
                 'auth_type' => 4,
                 'auth_name' => '声音验证',
                 'sum' => isset($statistics['noice_sum']) ? $statistics['noice_sum'] : 0
             )
         );

         $this->to_api_message(1, 'get_auth_statistics_success', $data);
     }

     public function statistics_by_date(){

        $data = array();

        $this->load->model('webModel/Auth_statistics','auth_statistics');

        //昨天
        $day_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $day_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $day_stat_info = $this->auth_statistics->get_ty_time($day_start, $day_end);

        //本周
        $timestamp = time();
        $week_start = date('Y-m-d', $timestamp-86400*date('w',$timestamp)+(date('w',$timestamp)>0?86400:-518400));  ;
        $week_start = strtotime($week_start);
        $week_end = $week_start + 518400;
        $week_stat_info = $this->auth_statistics->get_ty_time($week_start, $week_end);

        //本月
        $month_start = strtotime(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))));
        $month_end = strtotime(date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))));
        $month_stat_info = $this->auth_statistics->get_ty_time($month_start, $month_end);

        $data = array(
            array(
                'auth_type' => 1,
                'auth_name' => '普通验证',
                'day_count' => isset($day_stat_info['click_sum']) ? $day_stat_info['click_sum'] : 0,
                'week_count' => isset($week_stat_info['click_sum']) ? $week_stat_info['click_sum'] : 0,
                'month_count' => isset($month_stat_info['click_sum']) ? $month_stat_info['click_sum'] : 0
            ),
            array(
                'auth_type' => 2,
                'auth_name' => '手势验证',
                'day_count' => isset($day_stat_info['hand_sum']) ? $day_stat_info['hand_sum'] : 0,
                'week_count' => isset($week_stat_info['hand_sum']) ? $week_stat_info['hand_sum'] : 0,
                'month_count' => isset($month_stat_info['hand_sum']) ? $month_stat_info['hand_sum'] : 0
            ),
            array(
                'auth_type' => 3,
                'auth_name' => '人脸验证',
                'day_count' => isset($day_stat_info['face_sum']) ? $day_stat_info['face_sum'] : 0,
                'week_count' => isset($week_stat_info['face_sum']) ? $week_stat_info['face_sum'] : 0,
                'month_count' => isset($month_stat_info['face_sum']) ? $month_stat_info['face_sum'] : 0
            ),
            array(
                'auth_type' => 4,
                'auth_name' => '声音验证',
                'day_count' => isset($day_stat_info['noice_sum']) ? $day_stat_info['noice_sum'] : 0,
                'week_count' => isset($week_stat_info['noice_sum']) ? $week_stat_info['noice_sum'] : 0,
                'month_count' => isset($month_stat_info['noice_sum']) ? $month_stat_info['noice_sum'] : 0
            ),
        );

        $this->to_api_message(1, 'get_auth_statistics_success', $data);
     }

     /**
      * 统计详情
      * @param  int $power_id  权限ID
      * @return json
      */
     public function statistics_info(){
         $power_id = $this->input->get_post('power_id');
         $page = $this->input->get_post('page', TRUE);
         $page = $page ? $page : 1;
         $limit = 50;
         $offset = ($page-1)*$limit;

         //获取数据总数，分页使用
         $this->load->model('webModel/Auth_statistics','auth_statistics');

         $count = $this->auth_statistics->get_count($power_id);
         $page_count = ceil($count/$limit);

         $list = $this->auth_statistics->get_by_power($power_id, $limit, $offset);

         if(empty($list)){
             $this->to_api_message(1, 'get_auth_statistics_success', $list, true, $page_count);
         }

         $data = array();
         foreach($list as $auth){
             $temp = array();
             $temp = array(
                 'statistics_time' => $auth['statistics_time'],
                 'click_sum' => $auth['click_day_auth_count'],
                 'hand_sum' => $auth['hand_day_auth_count'],
                 'face_sum' => $auth['face_day_auth_count'],
                 'noice_sum' => $auth['noice_day_auth_count']
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_auth_statistics_success', $data, true, $page_count);
     }

}
