<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 公司信息设置接口
 */
class Company extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/Company_model','company');
    }

    /**
     * 获取公司信息
     */
    public function index(){
        $get = $this->company->get();

        if(empty($get)){
            $this->to_api_message(0, 'company_info_empty');
        }

        $data = array();
        $data = array(
            'id' => $get['id'],
            'company_name' => $get['name'],
            'company_intro' => $get['intro'],
            'company_logo' => $get['logo']
        );

        $this->to_api_message(1, 'get_company_info_success', $data);
    }

    /**
     * 设置公司信息
     * @param  string  $logo           公司logo
     * @param  string  $company_name   公司名称
     * @param  string  $company_intro  公司简介
     * @return json
     */
    public function set(){
        $company_name = $this->input->get_post('company_name', TRUE);
        $company_intro = $this->input->get_post('company_intro', TRUE);

        if ($this->form_validation->run() == FALSE){
            $error = validation_errors();
            $this->to_api_message(0, $error);
        }

        $data = array();

        if($company_name){
            $data['name'] = $company_name;
        }

        if($company_intro){
            $data['intro'] = $company_intro;
        }

        $company = $this->company->get();

        if(empty($company)){
            $affected = $this->company->insert($data);
        }else{
            $affected = $this->company->update($data);
        }

        $op_description = sprintf("设置了企业信息:%s", $company_name);
        if($affected){
            $this->add_op_log($op_description, 1);
            $this->to_api_message(1, 'update_company_info_success');
        }else{
            $this->add_op_log($op_description, 0);
            $this->to_api_message(0, 'update_company_info_failed');
        }
    }

    public function upload(){


        if(!empty($_FILES) && isset($_FILES['userfile']) && $_FILES['userfile']['error'] == 0){

            $config = array();
            $config = array(
                'upload_path'   => realpath(dirname(SELF)).'/resources/',
                'allowed_types' => 'gif|jpg|png',
                'max_size'      => '1024',
                'file_name'     => 'logo_'.time()
            );

            $this->load->library('upload', $config);
            if(!$this->upload->do_upload()) {
                $this->to_api_message(0, $this->upload->display_errors());
            }else{
                $upload_data = $this->upload->data();  //文件的一些信息

                $company = $this->company->get();
                $data = array();
                $data = array(
                    'logo' => $upload_data['file_name']
                );
                if(empty($company)){
                    $affected = $this->company->insert($data);
                }else{
                    $affected = $this->company->update($data);
                }

                $op_description = sprintf("设置了企业logo");
                if($affected){
                    $this->add_op_log($op_description, 1);
                    $this->to_api_message(1, 'update_company_info_success', $data);
                }else{
                    $this->add_op_log($op_description, 0);
                    $this->to_api_message(0, 'update_company_info_failed');
                }
            }
        }
    }

}
