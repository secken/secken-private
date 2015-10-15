<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户组接口
 */
class Group extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/Group_model', 'group');
    }

    /**
     * 企业组列表
     * @return json
     */
    public function index(){
        $list = $this->group->get_list();

        if(empty($list)){
            $this->to_api_message(1, 'get_group_list_success', $list);
        }

        $data = array();
        foreach($list as $group){
            $temp = array();
            $temp = array(
                'gid' => $group['gid'],
                'gname' => $group['name'],
                'inner' => $group['inner']
            );

            $data[] = $temp;
        }

        $this->to_api_message(1, 'get_group_list_success', $data);

    }

    /**
     * 组搜索
     * @param  string  $group_name  组名称
     * @return json
     */
    public function search(){
        $group_name = $this->input->get_post('group_name', TRUE);

        $list = $this->group->search($group_name);
        if(empty($list)){
            $this->to_api_message(1, 'get_group_list_success', $list);
        }

        $data = array();
        foreach($list as $group){
            $temp = array();
            $temp = array(
                'gid' => $group['gid'],
                'gname' => $group['name'],
                'inner' => $group['inner']
            );

            $data[] = $temp;
        }

        $this->to_api_message(1, 'get_group_list_success', $data);

    }

    /**
     * 添加组
     * @param  string $group_name  组名称
     * @return json
     */
     public function add(){
         $group_name = $this->input->get_post('group_name', TRUE);

         $insertData = array();
         $insertData = array(
             'name' => $group_name,
             'inner' => 0
         );

         $insert_id = $this->group->insert($insertData);

         if($insert_id > 0){
             $this->add_op_log("添加了分组:$group_name", 1);
             $this->to_api_message(1, 'insert_data_success');
         }else{
             $this->add_op_log("添加了分组:$group_name", 0);
             $this->to_api_message(0, 'insert_data_failed');
         }
     }

     /**
      * 修改组名称
      * @param  string $group_name 组名称
      * @param  int    $gid        组ID
      * @return json
      */
      public function edit(){

          $group_name = $this->input->get_post('group_name', TRUE);
          $gid = $this->input->get_post('gid', TRUE);

          //查询未修改前的分组名称
          $group_info = $this->group->get($gid);
          if(empty($group_info)){
              $this->to_api_message(0,'unknow_group');
          }

          $updateData = $where = array();
          $updateData = array(
              'name' => $group_name
          );

          $where = array(
              'gid' => $gid
          );

          $op_description = sprintf("修改了分组名称:%s 改为%s", $group_info['name'], $group_name);
          $update = $this->group->update($updateData, $where);
          if($update){
              $this->add_op_log($op_description, 1);
              $this->to_api_message(1, 'update_group_success');
          }else{
              $this->add_op_log($op_description, 0);
              $this->to_api_message(0, 'update_group_failed');
          }
      }

      /**
       * 删除组，用户自动归入未分组内,未分组不可删除
       * @param  int $gid 组ID
       * @return json
       */
       public function delete(){
           $gid = $this->input->get_post('gid', TRUE);

           $get = $this->group->get($gid);

           if(empty($get)){
               $this->to_api_message(0, 'unknow_group');
           }
           //内置组不可删除
           if($get['inner'] == 1){
               $this->to_api_message(0, 'inner_group_cannot_delete');
           }

           $where = array();
           $where = array(
               'gid' => $gid
           );

           $op_description = sprintf("删除分组:%s", $get['name']);

           $delete = $this->group->delete($where);
           if($delete){

               $this->load->model('webModel/Group_power','group_power');

               //检查组下是否已分配权限
               $get_group_power = $this->group_power->get($gid);
               if(!empty($get_group_power)){
                   //删除组下面的权限集合
                   $delete = $this->group_power->delete($where);
                   if($delete){
                       //将该组的用户迁移至默认组中
                       $this->load->model('webModel/User_group','user_group');
                       $updateData = $where = array();
                       $updateData = array(
                           'gid' => 1
                       );
                       $where = array(
                           'gid' => $gid
                       );

                       //检查组下是否有用户
                       $get_user_group = $this->user_group->get($gid);
                       if($get_user_group > 0){
                           $affected = $this->user_group->update($updateData, $where);
                           if($affected){
                               $this->add_op_log($op_description, 1);
                               $this->to_api_message(1, 'delete_group_success');
                           }else{
                               $this->add_op_log($op_description, 0);
                               $this->to_api_message(0, 'delete_group_failed');
                           }
                       }
                   }else{
                       $this->add_op_log($op_description, 0);
                       $this->to_api_message(0, 'delete_group_failed');
                   }
               }

               $this->add_op_log($op_description, 1);
               $this->to_api_message(1, 'delete_group_success');
           }else{
               $this->add_op_log($op_description, 0);
               $this->to_api_message(0, 'delete_group_failed');
           }
       }

      /**
       * 组内权限
       * @param  int $gid  组ID
       * @return json
       */
       public function get_power(){
           $gid = $this->input->get_post('gid', TRUE);

           $this->load->model('webModel/Group_power', 'group_power');
           $power = $this->group_power->get($gid);

           if(empty($power)){
               $this->to_api_message(1, 'get_group_power_success',array());
           }

           $data = array();
           foreach($power as $p){
               $temp = array();
               $temp = array(
                   'id' => $p['id'],
                   'power_name' => $p['name'],
                   'power_status' => $p['status']
               );

               $data[] = $temp;
           }

           $this->to_api_message(1, 'get_group_power_success', $data);

       }

       /**
        * 设置权限
        * @param int    $gid        组ID
        * @param string $power_ids  权限
        * @param return
        */
       public function set_power(){
           $gid = $this->input->get_post('gid', TRUE);
           $power_id = $this->input->get_post('power_id', TRUE);
           $set = $this->input->get_post('set', TRUE);

           $group_info = $this->group->get($gid);
           if(empty($group_info)){
               $this->to_api_message(0, 'unknow_group');
           }

           $this->load->model('webModel/Group_power','group_power');

           //查询权限名称,记录操作日志
           $this->load->model('webModel/Power_model', 'power');
           $powers = $this->power->get_powers_name($power_id);


           if($set == 1){

                $insertData = array();
                $insertData = array(
                    'gid' => $gid,
                    'power_id' => $power_id
                );

                $op_description = sprintf("%s设置分组权限信息:%s", $group_info['name'], $powers['name']);
                $insert = $this->group_power->insert($insertData);

                if($insert){
                    $this->add_op_log($op_description, 1);
                    $this->to_api_message(1, 'set_group_power_success');
                }else{
                    $this->add_op_log($op_description, 0);
                    $this->to_api_message(0, 'set_group_power_failed');
                }

            }else{

                $op_description = sprintf("%s移除分组权限信息:%s", $group_info['name'], $powers['name']);

                $delete = $this->group_power->delete($gid, $power_id);
                if($delete){
                    $this->add_op_log($op_description, 1);
                    $this->to_api_message(1, 'set_group_power_success');

                }else{
                    $this->add_op_log($op_description, 0);
                    $this->to_api_message(0, 'set_group_power_failed');
                }
           }
       }
}
