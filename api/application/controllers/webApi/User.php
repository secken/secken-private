<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 用户操作接口
 */
class User extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/User_model', 'user');
        $this->load->library('session');
    }

    public function destory_session(){
        $this->session->sess_destroy();

        $this->to_api_message(1, 'logout_success');
    }


    /**
     * 保存session信息
     * @param int $service_type 服务类型
     * @param int $identity_name 用户标示
     * @return json
     */
    public function save_session(){
        $service_type = $this->input->get_post('service_type', TRUE);
        $identity_name = $this->input->get_post('identity_name', TRUE);

        $where = array();
        if($service_type == 1){
            $where = array(
                'yangcong_uid' => $identity_name
            );
        }else{
            $where = array(
                'user_name' => $identity_name
            );
        }

        $user = $this->user->get_by_where($where);
        if(empty($user)){
            $this->to_api_message(0, 'unknow_user');
        }

        if($user[0]['is_admin'] == 0){
            $this->to_api_message(0, 'only_admin_login');
        }

        $this->load->model('webModel/Company_model','company');
        $get = $this->company->get();
        $company_logo = !empty($get) && !empty($get['logo']) ? $get['logo'] : '';

        $session_data = array(
            'user_id' => $user[0]['user_id'],
            'user_name' => $user[0]['user_name'],
            'true_name' => $user[0]['true_name'],
            'company_logo' => $company_logo
        );

        $this->session->set_userdata($session_data);
        $is_logined = $this->is_login();
        if($is_logined){
            $this->to_api_message(1, 'login_success', $session_data);
        }else{
            $this->to_api_message(0, 'login_failed');
        }
    }

    /**
     * 企业用户列表
     * @param int $gid  组ID
     * @param int $page 页码
     * @return json
     */
    public function index(){
        $gid = $this->input->get_post('gid', TRUE);
        $page = $this->input->get_post('page', TRUE);
        $page = $page ? $page : 1;
        $limit = 50;
        $offset = ($page-1)*$limit;

        //获取数据总数，分页使用
        $count = $this->user->get_count($gid);
        $page_count = ceil($count/$limit);
        $list = $this->user->get($gid, $limit, $offset);

        if(empty($list)){
            $this->to_api_message(1, 'get_list_success', $list);
        }

        $data = array();
        foreach($list as $user){
            $temp = array();
            $temp = array(
                'user_id' => $user['user_id'],
                'user_name' => $user['user_name'],
                'true_name' => $user['true_name'],
                'intro' => $user['intro'],
                'phone' => $user['phone'],
                'status' => $user['is_open'] == 1 ? '启用' : '禁用',
                'update_time' => $user['update_time'],
                'create_time' => $user['create_time'],
                'gid' => $user['gid']
            );

            $data[] = $temp;
        }

        $this->to_api_message(1, 'get_list_success', $data, true, $page_count);
    }



    /**
     * 添加企业用户
     * @param string $user_name  用户名
     * @param string $phone    手机号
     * @param string $true_name  姓名
     * @param string $intro     简介
     * @param int    $is_admin  是否设置为管理员(可选)
     * @return json
     */
    public function add(){
        $gid = $this->input->get_post('gid', TRUE);
        $user_name = $this->input->get_post('user_name', TRUE);
        $phone = $this->input->get_post('phone', TRUE);
        $true_name = $this->input->get_post('true_name', TRUE);
        $intro = $this->input->get_post('intro', TRUE);
        $intro = $intro === NULL ? '没有添加简介' : $intro;
        $is_admin = $this->input->get_post('is_admin', TRUE);

        if ($this->form_validation->run() == FALSE){
            $error = validation_errors();
            $this->to_api_message(0, $error);
        }

        $gid = $gid ? $gid : 1;

        if($is_admin == 1){
            //检查管理员是否已经存在
            $admin = $this->user->get_admin_user();
            if(!empty($admin)){
                $this->to_api_message(0, 'admin_is_exists');
            }
        }
        //检查用户名
        $check = $this->user->check_user_name($user_name);
        if($check){
            $this->to_api_message(0, 'username_is_exists');
        }

        //检查手机号
        $this->load->helper('phone');
        $valid_phone = valid_phone($phone);
        if($valid_phone === FALSE){
            $this->to_api_message(0, 'phone_invalid');
        }
        $check = $this->user->check_phone($phone);
        if($check){
            $this->to_api_message(0, 'phone_is_exists');
        }

        $this->load->model('webModel/Setting_model', 'setting');
        $setting = $this->setting->get();

        $identity_name = '';
        if($setting['service_type'] == 1){

            $app_info = array();
            $app_info = array(
                'app_id' => $setting['app_id'],
                'app_key' => $setting['app_key'],
                'use_private' => FALSE
            );

            $this->load->library('Secken', $app_info);

            $result = $this->secken->exchangeUid($phone);
            //var_dump($result);
            $respone_code = $this->secken->getCode();
            $identity_name = '';
            if($respone_code == 200){
                $identity_name = $result['uids'][0];
            }else{
                $this->to_api_message(0, 'secken_config_error');
            }

            if(empty($identity_name)){
                $this->to_api_message(0, 'please_login_on_yangcong');
            }
        }

        $insertData = array();
        $insertData = array(
            'user_name' => $user_name,
            'phone' => $phone,
            'true_name' => $true_name,
            'yangcong_uid' => $identity_name,
            'intro' => $intro,
            'is_open' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
            'is_admin' => $is_admin ? 1 : 0
        );

        $op_description = sprintf("添加了新用户:%s", $user_name);

        $insert_id = $this->user->insert($insertData);
        if($insert_id > 0){
            //添加用户到默认组
            $this->load->model('webModel/User_group','user_group');
            $insertData = array();
            $insertData = array(
                'user_id' => $insert_id,
                'gid' => $gid
            );
            $insert_id = $this->user_group->insert($insertData);
            if($insert_id > 0){
                $this->add_op_log($op_description, 1);
                $this->to_api_message(1, 'insert_data_success');
            }else{
                $this->add_op_log($op_description, 0);
                $this->to_api_message(0, 'insert_data_failed');
            }
        }else{
            $this->add_op_log($op_description, 0);
            $this->to_api_message(0, 'insert_data_failed');
        }
    }

    /**
     * 修改企业用户信息
     * @param string $true_name  姓名
     * @param int    $status    账号启用状态 0为封闭，1为开启
     * @param int    $uid       用户ID
     * @return json
     */

     public function edit(){

         $true_name = $this->input->get_post('true_name', TRUE);
         $open = $this->input->get_post('open', TRUE);
         $uid = $this->input->get_post('uid', TRUE);

         if ($this->form_validation->run() == FALSE){
             $error = validation_errors();
             $this->to_api_message(0, $error);
         }

         $user_info = $this->user->get_user_by_id(array($uid));
         if(empty($user_info)){
             $this->to_api_message(0, 'unknow_user');
         }

         $updateData = $where = array();
         $updateData = array(
             'true_name' => $true_name,
             'is_open' => $open,
             'update_time' => date('Y-m-d H:i:s')
         );
         $where = array(
             'user_id' => $uid
         );

         $op_description = sprintf("将%s修改为%s", $user_info[0]['true_name'], $true_name);
         $update = $this->user->update($updateData, $where);
         if($update){
             $this->add_op_log($op_description, 1);
             $this->to_api_message(1, 'update_userinfo_success');
         }else{
             $this->add_op_log($op_description, 0);
             $this->to_api_message(0, 'update_userinfo_failed');
         }
     }

    /**
     * 删除用户信息
     * @param  array $uids 需要被删除的用户id
     * @return json
     */
     public function delete(){
         $uids = $this->input->get_post('uids', TRUE);

         if ($this->form_validation->run() == FALSE){
             $error = validation_errors();
             $this->to_api_message(0, $error);
         }

         $user_ids = array();

         if(strpos($uids,'-') != FALSE){
             $uids = explode('-', $uids);
             foreach($uids as $uid){
                 $uid = intval($uid);
                 if($uid > 0){
                     $user_ids[] = $uid;
                 }
             }
         }else{
             $uids = intval($uids);
             if($uids >0){
                 $user_ids[] = $uids;
             }
         }

         //去除管理员账号
         $admin_user = $this->user->get_admin_user();
         if(!empty($admin_user)){
             $admin = array();
             foreach($admin_user as $user){
                 $admin[] = $user['user_id'];
             }

             $user_ids = array_diff($user_ids, $admin);
         }

         $user_infos = $this->user->get_user_by_id($user_ids);
         $user_info_string = '';
         foreach($user_infos as $user_info){
             $user_info_string .= $user_info['user_name'] . ',';
         }
         $user_info_string = rtrim($user_info_string, ',');
         $op_description = sprintf("删除了用户:%s", $user_info_string);

         $delete = $this->user->delete($user_ids);
         if($delete){
             $this->load->model('webModel/User_group','user_group');
             $delete = $this->user_group->delete($user_ids);

             if($delete){
                 $this->add_op_log($op_description, 1);
                 $this->to_api_message(1, 'delete_user_success');
             }else{
                 $this->add_op_log($op_description, 0);
                 $this->to_api_message(0, 'delete_user_failed');
             }
         }else{
             $this->to_api_message(0, 'delete_user_failed');
         }

     }

     /**
      * 移动用户到其它组
      * @param  array $uids 需要移动组的用户
      * @param  int   $gid  组ID
      * @return json
      */
      public function move_to_anthor_group(){

          $uids = $this->input->get_post('uids', TRUE);
          $gid = $this->input->get_post('gid', TRUE);

          if ($this->form_validation->run() == FALSE){
              $error = validation_errors();
              $this->to_api_message(0, $error);
          }

          $user_ids = array();
          if(strpos($uids,'-') != FALSE){
              $uids = explode('-', $uids);
              foreach($uids as $uid){
                  $uid = intval($uid);
                  if($uid > 0){
                      $user_ids[] = $uid;
                  }
              }
          }else{
              $uids = intval($uids);
              if($uids >0){
                  $user_ids[] = $uids;
              }
          }

          if(empty($user_ids)){
              $this->to_api_message(0, 'param_invild');
          }

          //验证组信息是否存在
          $this->load->model('webModel/Group_model', 'group');
          $get = $this->group->get($gid);

          if(empty($get)){
              $this->to_api_message(0, 'unknow_group');
          }

          $this->load->model('webModel/User_group','user_group');
          $updateData = $where = array();
          $updateData = array(
              'gid' => $gid
          );

          $user_infos = $this->user->get_user_by_id($user_ids);
          $user_info_string = '';
          foreach($user_infos as $user_info){
              $user_info_string .= $user_info['user_name'] . ',';
          }
          $user_info_string = rtrim($user_info_string, ',');

          $op_description = sprintf("将%s用户转移到%s组", $user_info_string, $get['name']);

          $update = $this->user_group->update($updateData, $user_ids);
          if($update){
              $this->add_op_log($op_description, 1);
              $this->to_api_message(1, 'update_user_group_success');
          }else{
              $this->add_op_log($op_description, 0);
              $this->to_api_message(0, 'update_user_group_failed');
          }
      }

      /**
       * 搜索组内用户
       * @param  string $wd   搜索的内容，目前可以搜索姓名和手机号
       * @param  string $gid  组ID
       * @param  int    $page 页码
       * @return json
       */
       public function search(){
           $wd = $this->input->get_post('wd', TRUE);
           $gid = $this->input->get_post('gid', TRUE);
           $page = $this->input->get_post('page', TRUE);
           $page = $page ? $page : 1;
           $limit = 50;

           $offset = ($page-1)*$limit;

           $this->load->helper('phone');
           $is_phone = valid_phone($wd);

           $count = $this->user->get_search_count($wd, $gid, $is_phone);
           $page_count = ceil($count/$limit);
           $list = $this->user->search($wd, $gid, $limit, $offset, $is_phone);

           if(empty($list)){
               $this->to_api_message(1, 'get_list_success', $list);
           }

           $data = array();
           foreach($list as $user){
               $temp = array();
               $temp = array(
                   'user_id' => intval($user['user_id']),
                   'user_name' => $user['user_name'],
                   'true_name' => $user['true_name'],
                   'intro' => $user['intro'],
                   'phone' => $user['phone'],
                   'status' => $user['is_open'] == 1 ? '启用' : '禁用',
                   'update_time' => $user['update_time'],
                   'create_time' => $user['create_time'],
                   'gid' => $user['gid']
               );

               $data[] = $temp;
           }

           $this->to_api_message(1, 'get_list_success', $data, true, $page_count);
       }

       /**
        * 获取管理员用户
        */
        public function get_admin_user(){

            $data = array();

            $admin = $this->user->get_admin_user();
            if(!empty($admin)){
                foreach($admin as $user){
                    $data = array(
                        'user_id' => $user['user_id'],
                        'user_name' => $user['user_name'],
                        'phone' => $user['phone'],
                        'create_time' => $user['create_time']
                    );
                }
            }

            $this->to_api_message(1, 'get_admin_user_success', $data);

        }

       /**
        * 导入用户
        *
        */
        public function import_user(){

            if(!empty($_FILES) && isset($_FILES['userfile']) && $_FILES['userfile']['error'] == 0){

                $config = array();
                $config = array(
                    'upload_path'   => realpath(dirname(SELF)).'/resources/',
                    'allowed_types' => 'xls',
                    'max_size'      => '2048',
                    'file_name'     => 'user_'.time()
                );

                $this->load->library('upload', $config);
                if(!$this->upload->do_upload()) {
                    $this->to_api_message(0, $this->upload->display_errors());
                }else{
                    $upload_data = $this->upload->data();  //文件的一些信息
                    $excel_path = $upload_data['full_path'];

                    require_once APPPATH . '/libraries/PHPExcel.php';
                    require_once APPPATH . '/libraries/PHPExcel/IOFactory.php';

                    $objReader = IOFactory::createReader('Excel5');
                    $objPHPExcel = $objReader->load($excel_path);
                    $sheet = $objPHPExcel->getSheet(0); // 读取第一工作表
                    $highestRow = $sheet->getHighestRow(); // 取得总行数
                    $highestColumm = $sheet->getHighestColumn(); // 取得总列数


                    $this->load->model('webModel/User_group','user_group');
                    $this->load->model('webModel/Setting_model', 'setting');
                    $setting = $this->setting->get();

                    $identity_name = '';
                    if($setting['service_type'] == 1){

                        $app_info = array();
                        $app_info = array(
                            'app_id' => $setting['app_id'],
                            'app_key' => $setting['app_key'],
                            'use_private' => FALSE
                        );

                        $this->load->library('Secken', $app_info);
                    }

                    $error = array();
                    $success_row = $error_row = 0;
                    for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
                        if($row == 1){
                            continue;
                        }

                        $user_name = $sheet->getCell('A'.$row)->getValue();
                        $true_name = $sheet->getCell('B'.$row)->getValue();
                        $phone = $sheet->getCell('C'.$row)->getValue();

                        //检查用户名
                        $check = $this->user->check_user_name($user_name);
                        if($check){
                            $error_row++;
                            $error[] = array(
                                'row' => $row,
                                'user_name' => $user_name,
                                'true_name' => $true_name,
                                'phone' => $phone,
                                'error' => $this->lang->line('username_is_exists')
                            );
                            continue;
                        }

                        //检查手机号
                        $this->load->helper('phone');
                        $valid_phone = valid_phone($phone);
                        if($valid_phone === FALSE){
                            $error_row++;
                            $error[] = array(
                                'row' => $row,
                                'user_name' => $user_name,
                                'true_name' => $true_name,
                                'phone' => $phone,
                                'error' => $this->lang->line('phone_invalid')
                            );
                            continue;
                        }
                        $check = $this->user->check_phone($phone);
                        if($check){
                            $error_row++;
                            $error[] = array(
                                'row' => $row,
                                'user_name' => $user_name,
                                'true_name' => $true_name,
                                'phone' => $phone,
                                'error' => $this->lang->line('phone_is_exists')
                            );
                            continue;
                        }

                        if($this->secken){
                            settype($phone, "string");
                            $result = $this->secken->exchangeUid($phone);
                            $respone_code = $this->secken->getCode();

                            $identity_name = '';
                            if($respone_code == 200){
                                $identity_name = $result['uids'][0];
                                if(empty($identity_name)){
                                    $error_row++;
                                    $error[] = array(
                                        'row' => $row,
                                        'user_name' => $user_name,
                                        'true_name' => $true_name,
                                        'phone' => $phone,
                                        'error' => $this->lang->line('please_login_on_yangcong')
                                    );
                                    break;
                                }
                            }else{
                                $error_row++;
                                $error[] = array(
                                    'row' => $row,
                                    'user_name' => $user_name,
                                    'true_name' => $true_name,
                                    'phone' => $phone,
                                    'error' => $this->lang->line('secken_config_error')
                                );
                                break;
                            }
                        }else{
                            $error_row++;
                            $error[] = array(
                                'row' => $row,
                                'user_name' => $user_name,
                                'true_name' => $true_name,
                                'phone' => $phone,
                                'error' => $this->lang->line('secken_config_error')
                            );
                            break;
                        }

                        $insertData = array();
                        $insertData = array(
                            'user_name' => $user_name,
                            'phone' => $phone,
                            'true_name' => $true_name,
                            'yangcong_uid' => $identity_name,
                            'create_time' => date('Y-m-d H:i:s'),
                            'update_time' => date('Y-m-d H:i:s'),
                        );

                        $op_description = sprintf("导入了新用户:%s", $user_name);
                        $insert_id = $this->user->insert($insertData);
                        if($insert_id){
                            $insertData = array();
                            $insertData = array(
                                'user_id' => $insert_id,
                                'gid' => 1
                            );
                            $insert_id = $this->user_group->insert($insertData);
                            if($insert_id){
                                $success_row++;
                                $this->add_op_log($op_description, 1);
                            }else{
                                $error_row++;
                                $this->add_op_log($op_description, 0);
                            }
                        }else{
                            $error_row++;
                            $this->add_op_log($op_description, 0);
                        }
                    }

                    $data = array();
                    $data = array(
                        'success_row' => $success_row,
                        'error_row' => $error_row,
                        'error' => $error
                    );

                    $this->to_api_message(1, 'import_user', $data);
                }
            }
        }
}
