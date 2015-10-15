<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 更新
 */
class Upgrade extends API_Controller{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 检查更新
     */
    public function check(){
        $dependent_code = $this->input->get_post('dependent_code', TRUE);
        $version_code = $this->input->get_post('version_code', TRUE);

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

        $upgrade = $this->secken->checkUpgrade($dependent_code, $version_code);
        var_dump($upgrade);
        if(is_array($upgrade)){
            $status = isset($upgrade['update']) && $upgrade['update'] == 'NO' ? 0 : 1;

            $data = array();
            if($status == 1){
                $data = array(
                    'old' => $upgrade['old'],
                    'lastest' => $upgrade['lastest'],
                    'show_version' => $upgrade['show_version'],
                    'summary' => $upgrade['summary'],
                    'download' => urlencode($upgrade['download'])
                );

                $this->to_api_message(1, 'check_version_upgrade', $data);

            }else{
                $this->to_api_message(0, 'check_version_upgrade');
            }
        }else{
            $this->to_api_message(0, 'check_version_upgrade');
        }
    }

    /**
     * 下载更新压缩包
     *
     */
    public function download(){
        $download_url = $this->input->get_post('download');
        $decode_download = urldecode($download_url);

        $zip_name = basename($decode_download);

        $download_file = APPPATH . 'cache/'.$zip_name;

        $fp_output = fopen($download_file, 'w');
        $ch = curl_init($download_url);
        curl_setopt($ch, CURLOPT_FILE, $fp_output);
        curl_exec($ch);
        curl_close($ch);

        if(file_exists($download_file)){
            $this->to_api_message(1, 'download_success');
        }else{
            $this->to_api_message(0, 'download_failed');
        }
    }

    /**
     * 更新文件
     */
    public function update(){
        $upgrade = $this->input->get_post('upgrade');

        $decode_download = urldecode($upgrade['download']);
        $zip_name = basename($decode_download);

         $zip_file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $zip_name;
        if(!file_exists($zip_file)){
            $this->to_api_message(0, 'zip_not_exist');
        }


        $zip=new ZipArchive;//新建一个ZipArchive的对象
	    if($zip->open($zip_file)===TRUE){
	        $zip->extractTo(APPPATH . 'cache' . DIRECTORY_SEPARATOR);//假设解压缩到在当前路径下images文件夹内
	        $zip->close();//关闭处理的zip文件

            @unlink($zip_file);
        }

        $upgrade_file = array();

        $upgrade_file_basename = pathinfo($zip_file, PATHINFO_BASENAME);
        $upgrade_file_arr = explode('.',$upgrade_file_basename);
        $ext = end($upgrade_file_arr);
        $upgrade_dirname = rtrim($upgrade_file_basename, '.'.$ext);

        $ori_dir = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $upgrade_dirname . DIRECTORY_SEPARATOR;
        $target_dir = realpath(dirname(dirname(APPPATH))) . DIRECTORY_SEPARATOR;

        $update_files = array();
        $this->update_file($update_files, $ori_dir, $target_dir);


        if(!empty($update_files)){
            $this->load->model('webModel/Version_model','version');

            $updateData = $where = array();
            $updateData = array(
                'version_name' => $upgrade['show_version'],
                'version_code' => $upgrade['lastest'],
                'upgrade_content' => $upgrade['summary'],
                'upgrade_time' => date('Y-m-d H:i:s')
            );
            $where = array(
                'version_code' => $upgrade['old']
            );
            $affected_rows = $this->version->update($updateData, $where);
            if($affected_rows){
                $this->to_api_message(1, 'update_file_success', $update_files);
            }
        }

        $this->to_api_message(0, 'update_file_failed');

    }

    private function update_file(&$update_files, $ori_dir, $target_dir){

        $files = array();
        $this->read_dir($files, $ori_dir);

        if(!empty($files)){
            foreach($files as $ori_file){
                $file = str_replace($ori_dir, '', $ori_file);
                $target_file = $target_dir . $file;

                if(is_file($target_file)){
                    if(!move_uploaded_file($ori_file, $target_file)){
                        copy($ori_file, $target_file);
                    }
                    $update_files[] = array(
                        'file' => $file,
                        'upgrade_type' => 1
                    );
                }else{
                    if(!move_uploaded_file($ori_file, $target_file)){
                        copy($ori_file, $target_file);
                    }

                    $update_files[] = array(
                        'file' => $file,
                        'upgrade_type' => 0
                    );
                }
            }
        }
    }

    private function read_dir(&$fileInfo, $dir){

        foreach (glob($dir.'*', GLOB_MARK) as $v) {

            if(is_dir($v)){
                $this->read_dir($fileInfo, $v);
            }else{
                $fileInfo[] = $v;
            }
        }
    }
}
