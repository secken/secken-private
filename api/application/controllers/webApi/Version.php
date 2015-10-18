<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 版本接口
 */
class Version extends API_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('webModel/version_model', 'version');
    }

    public function get_version_info(){

        $list = $this->version->get_list();
        if(empty($list)){
            $this->to_api_message(1, 'get_version_info_success');
        }
        $data = array();
        foreach($list as $version){
            $dataTemp = array();
            $dataTemp = array(
                'dependent_info' => $version['dependent_info'],
                'dependent_code' => $version['dependent_code'],
                'version_name' => $version['version_name'],
                'version_code' => $version['version_code']
            );

            $data[] = $dataTemp;
        }

        $this->to_api_message(1, 'get_version_info_success', $data);
    }
}
