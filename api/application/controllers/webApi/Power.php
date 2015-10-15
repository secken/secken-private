<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Power extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/Power_model','power');
    }

    /**
     * 权限列表
     * @return json
     */
     public function index(){
         $page = $this->input->get_post('page', TRUE);
         $page = $page ? $page : 1;
         $limit = 50;
         $offset = ($page-1)*$limit;

         $count = $this->power->get_count();
         $page_count = ceil($count/$limit);

         $list = $this->power->get_list($limit, $offset);

         if(empty($list)){
             $this->to_api_message(1, 'get_power_list_success', $list);
         }

         $data = array();
         foreach($list as $power){
             $temp = array();
             $temp = array(
                 'id' => $power['id'],
                 'name' => $power['name'],
                 'intro' => $power['intro'],
                 'status' => $power['status'],
                 'power_id' => $power['power_id'],
                 'power_key' => $power['power_key']
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_power_list_success', $data, true, $page_count);

     }

    /**
     * 添加权限
     * @param string $power_name  权限名称
     * @param string $power_intro 权限简介
     */
     public function add(){
         $power_name = $this->input->get_post('power_name', TRUE);
         $power_intro = $this->input->get_post('power_intro', TRUE);

         $insertData = array();
         $insertData = array(
             'name' => $power_name,
             'intro' => $power_intro,
             'status' => 1
         );

         $this->load->helper('rand');
         $insertData['power_id'] = create_rand_string(20);
         $insertData['power_key'] = create_rand_string(32);

         $op_description = sprintf("添加权限信息:%s", $power_name);

         $insert_id = $this->power->insert($insertData);
         if($insert_id > 0){
             $this->add_op_log($op_description, 1);
             $this->to_api_message(1, 'insert_data_success');
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'insert_data_failed');
         }
     }

    /**
     * 配置权限开关
     * @param  int $power_id 权限ID
     * @return json
     */
     public function power_switch(){
         $power_id = $this->input->get_post('power_id');

         $power = $this->power->get($power_id);
         if(empty($power)){
             $this->to_api_message(0, 'unknow_power');
         }

         $updateData = $where = array();
         $updateData = array(
             'status' => $power['status'] == 1 ? 0 : 1
         );

         $where = array(
             'id' => $power_id
         );

         $op_description = sprintf("将权限%s开关设置为%s", $power['name'], $updateData['status'], 1);

         $update = $this->power->update($updateData, $where);
         if($update){
             $data = array();
             $data = array(
                 'current_power' => $updateData['status']
             );

             $this->add_op_log($op_description, 1);
             $this->to_api_message(1, 'power_status_update_success', $data);
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'power_status_update_failed');
         }

     }

    /**
     * 删除权限
     * @param int $power_id 权限ID
     * @return json
     */
     public function delete(){
         $power_id = $this->input->get_post('power_id');

         $power = $this->power->get($power_id);
         if(empty($power)){
             $this->to_api_message(0, 'unknow_power');
         }

         $where = array();
         $where = array(
             'id' => $power_id
         );

         $op_description = sprintf("删除了%s权限信息", $power['name']);

         $delete = $this->power->delete($where);
         if($delete){
             $this->load->model('webModel/Group_power', 'group_power');
             $where = array();
             $where = array(
                 'power_id' => $power_id
             );
             $delete = $this->group_power->delete($where);
             if($delete){
                 $this->add_op_log($op_description, 1);
                 $this->to_api_message(1, 'delete_power_success');
             }else{
                 $this->add_op_log($op_description, 0);
                 $this->to_api_message(0, 'delete_power_failed');
             }
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'delete_power_failed');
         }
     }

    /**
     * 搜索权限
     * @param  string $power_name 权限名称
     * @return json
     */
     public function search(){
         $power_name = $this->input->get_post('power_name');
         $page = $this->input->get_post('page', TRUE);
         $page = $page ? $page : 1;
         $limit = 50;

         $offset = ($page-1)*$limit;

         $count = $this->power->get_search_count($power_name);
         $page_count = ceil($count/$limit);

         $list = $this->power->search($power_name);

         if(empty($list)){
             $this->to_api_message(1, 'get_power_list_success', $list);
         }

         $data = array();
         foreach($list as $power){
             $temp = array();
             $temp = array(
                 'name' => $power['name'],
                 'intro' => $power['intro'],
                 'status' => $power['status'],
                 'power_id' => $power['power_id'],
                 'power_key' => $power['power_key']
             );

             $data[] = $temp;
         }

         $this->to_api_message(1, 'get_power_list_success', $data, true, $page_count);

     }

    /**
     * 修改权限信息
     * @param  string $power_name  权限名称
     * @param  string $power_intro 权限简介
     * @param  int    $power_id    权限ID
     * @return json
     */
     public function edit(){
         $power_name = $this->input->get_post('power_name', TRUE);
         $power_intro = $this->input->get_post('power_intro', TRUE);
         $power_status = $this->input->get_post('power_status', TRUE);
         $power_id = $this->input->get_post('power_id', TRUE);

         $power = $this->power->get($power_id);
         if(empty($power)){
             $this->to_api_message(0, 'unknow_power');
         }


         $updateData = $where = array();
         $updateData = array(
             'name' => $power_name,
             'intro' => $power_intro,
             'status' => $power_status
         );

         $where = array(
             'id' => $power_id
         );

         $op_description = sprintf("将%s权限修改为%s", $power['name'], $power_name);

         $update = $this->power->update($updateData, $where);
         if($update){
             $this->add_op_log($op_description, 1);
             $this->to_api_message(1, 'update_power_success');
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'update_power_failed');
         }
     }

    /**
     * 重新生成授权信息
     * @param  int    $power_id    权限ID
     * @return json
     */
     public function regen_auth_key(){
         $power_id = $this->input->get_post('id', TRUE);

         $power = $this->power->get($power_id);
         if(empty($power)){
             $this->to_api_message(0, 'unknow_power');
         }

         $this->load->helper('rand');

         $updateData = $where = array();
         $updateData = array(
             'power_id' => create_rand_string(20),
             'power_key' => create_rand_string(32),
         );

         $where = array(
             'id' => $power_id
         );

         $op_description = sprintf("修改了%s权限的授权信息", $power['name']);
         $update = $this->power->update($updateData, $where);
         if($update){
             $this->add_op_log($op_description, 1);
             $this->to_api_message(1, 'update_power_success', $updateData);
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'update_power_failed');
         }
     }
}
