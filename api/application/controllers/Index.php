<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Index extends API_Controller {

    public function __construct(){
        parent::__construct();

        if(!in_array($this->uri->segment(2), array('check', 'addconfig', 'checkinstall'))){
            $this->load->dbforge();
        }
    }

    //检查环境
    public function check(){
        $data = array();

        $status = 1;

        //输出基本配置信息
        $data['env']['操作系统'] = array(
            'need' => '不限制',
            'best' => '类Unix',
            'current' => PHP_OS
        );
        $data['env']['PHP'] = array(
            'need' => '5.2',
            'best' => '5.4 or new',
            'current' => PHP_VERSION
        );

        if(function_exists('mysqli_get_client_version')){
            $current = 'mysqli:'. mysqli_get_client_version();
        }elseif(function_exists('mysql_get_client_version')){
            $current = 'mysql:' . mysql_get_client_info();
        }else{
            $current = '未安装';
            $status = 0;
        }

        $data['env']['数据库'] = array(
            'need' => '与php版本对应',
            'best' => '与php版本对应',
            'current' => $current
        );


        $gd_info = array();
        if(function_exists('gd_info')){
            $gd_info = gd_info();
        }else{
            $status = 0;
        }

        $data['env']['GD库'] = array(
            'need' => '1.0',
            'best' => '2.0',
            'current' => isset($gd_info['GD Version']) ? $gd_info['GD Version'] : '未安装'
        );

        $curl_version = array();
        if(function_exists('curl_version')){
            $curl_version = curl_version();
        }else{
            $status = 0;
        }

        $data['env']['CURL扩展'] = array(
            'need' => '与php版本对应',
            'best' => '与php版本对应',
            'current' => isset($curl_version['version']) ? $curl_version['version'] : '未安装'
        );

        if(class_exists('ZipArchive')){
            $current = '已安装';
        }else{
            $current = '未安装';
            $status = 0;
        }

        $data['env']['ZIP扩展'] = array(
            'need'=>'与php版本对应',
            'best' => '与php版本对应',
            'current' => $current
        );
        //检查文件是否可写
        $check_write_file = array();
        $check_write_file = array(
            'api/application/config/database.php' => APPPATH . 'config/database.php',
            'api/application/cache' => APPPATH . 'cache',
            'api/application/logs' => APPPATH . 'logs',
            'api/resources' => dirname(APPPATH) . '/resources'
        );

        foreach($check_write_file as $item => $file){

            if(is_writable($file)){
                $is_writable = 1;
            }else{
                $is_writable = 0;
                $status = 0;
            }
            $data['writeable'][$item] = $is_writable;
        }

        $data['allow_next'] = $status;

        echo $this->to_api_message(1, 'check_env', $data);
    }

    //添加数据库配置文件
    public function add_database_config_file(){
        $host_name = $this->input->get_post('host_name', TRUE);
        $db_name = $this->input->get_post('db_name', TRUE);
        $db_user = $this->input->get_post('db_user', TRUE);
        $db_pwd = $this->input->get_post('db_pwd', TRUE);
        $db_pre = $this->input->get_post('db_pre', TRUE);

        if ($this->form_validation->run() == FALSE){
            $error = validation_errors();
            $this->to_api_message(0, $error);
        }

        $host_name = $host_name ? $host_name : '127.0.0.1';
        $db_name = $db_name ? $db_name : 'yangcong_private_cloud';
        $db_user = $db_user ? $db_user : 'root';
        $db_pre = $db_pre ? $db_pre . '_' : 'pc_';

        if(function_exists('mysqli_get_client_version')){
            $dbdriver = 'mysqli';
        }else{
            $dbdriver = 'mysql';
        }


        $content = <<<EOT
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

\$active_group = 'default';
\$query_builder = TRUE;

\$db['default'] = array(
	'dsn'	=> '',
	'hostname' => "$host_name",
	'username' => "$db_user",
	'password' => "$db_pwd",
	'database' => "$db_name",
	'dbdriver' => "$dbdriver",
	'dbprefix' => "$db_pre",
	'pconnect' => FALSE,
	'db_debug' => TRUE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

EOT;
    	$content .= "//\r\n\r\n?>";
    	file_put_contents(APPPATH . 'config/database.php', $content);
        $config = array(
            'dsn'   => '',
            'hostname' => "$host_name",
            'username' => "$db_user",
            'password' => "$db_pwd",
            'database' => '',
            'dbdriver' => "$dbdriver",
            'dbprefix' => "$db_pre",
            'pconnect' => FALSE,
            'db_debug' => TRUE,
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt' => FALSE,
            'compress' => FALSE,
            'stricton' => FALSE,
            'failover' => array(),
            'save_queries' => TRUE
        );

        $this->load->database($config);
        $this->load->dbforge();
        $create = $this->dbforge->create_database($db_name);
        if($create){
            $this->to_api_message(1, 'create_database_success');
        }else{
            $this->to_api_message(0, 'create_database_failed');
        }
    }


    /**
     * 添加表结构
     */
    public function add_table(){
        //添加数据库以及数据表和默认数据
        $current_table = array();
        $current_table = array(
            'table_user', 'table_group_info', 'table_user_group',
            'table_power', 'table_group_power', 'table_auth_log',
            'table_op_log', 'table_version', 'table_company','table_setting',
            'table_user_event', 'table_auth_statistics','table_session'
        );

        foreach($current_table as $table_name){
            $this->$table_name();
        }

        //初始化数据
        $this->add_default_data();

        $this->to_api_message(1, 'add_table_success');
    }

    //用户表
    private function table_user(){
        $fields = array();
        $fields = array(
            'user_id' => array('type' => 'INT','constraint'=> 11,'unsigned' => TRUE, 'auto_increment' => TRUE),
            'user_name' => array('type' => 'VARCHAR','constraint' => 20),
            'yangcong_uid' => array('type' => 'VARCHAR','constraint' => 64),
            'true_name' => array('type' => 'VARCHAR','constraint' => 20),
            'phone' => array('type' => 'VARCHAR','constraint' => 11),
            'intro' => array('type' => 'VARCHAR','constraint' => 100),
            'is_open' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE, 'default' => 1),
            'create_time' => array('type' => 'DATETIME'),
            'update_time' => array('type' => 'DATETIME'),
            'is_admin' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE, 'default' => 0)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('user_id',TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('user', TRUE, $attributes);
    }

    //分组
    private function table_group_info(){
        $fields = array();
        $fields = array(
            'gid' => array('type' => 'SMALLINT','unsigned' => TRUE,'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR','constraint' => 20),
            'inner' => array('type' => 'TINYINT','constraint' => 1, 'unsigned'=>TRUE)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('gid',TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('group_info', TRUE, $attributes);
    }

    //用户组
    private function table_user_group(){
        $fields = array();
        $fields = array(
            'user_id' => array('type' => 'INT','constraint'=> 11,'unsigned' => TRUE),
            'gid' => array('type' => 'SMALLINT','unsigned' => TRUE)
        );

        $this->dbforge->add_field($fields);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('user_group', TRUE, $attributes);
    }

    //权限表
    private function table_power(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' =>  20),
            'intro' => array('type' => 'VARCHAR', 'constraint' =>  50),
            'status' => array('type' => 'TINYINT', 'constraint' => 1,'default' => 0),
            'power_id' => array('type' => 'VARCHAR', 'constraint' =>  20),
            'power_key' => array('type' => 'VARCHAR', 'constraint' =>  32)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('power', TRUE, $attributes);
    }

    //组权限
    private function table_group_power(){
        $fields = array();
        $fields = array(
            'gid' => array('type' => 'smallint', 'unsigned' => TRUE),
            'power_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE)
        );

        $this->dbforge->add_field($fields);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('group_power', TRUE, $attributes);
    }

    private function table_auth_log(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'auth_user' => array('type' => 'VARCHAR', 'constraint' => 20),
            'auth_type' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE),
            'power_name' => array('type' => 'VARCHAR', 'constraint' => 20),
            'power_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE),
            'event_id' => array('type' => 'VARCHAR', 'constraint' => 20),
            'auth_result' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE, 'default' => 0),
            'auth_time' => array('type' => 'DATETIME')
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('auth_log', TRUE, $attributes);
    }

    //操作日志表
    private function table_op_log(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE,'auto_increment' => TRUE),
            'op_user' => array('type' => 'VARCHAR', 'constraint' => 20),
            'op_name' => array('type' => 'VARCHAR', 'constraint' => 20),
            'op_intro' => array('type' => 'VARCHAR', 'constraint' => 50),
            'op_time' => array('type' => 'DATETIME'),
            'op_status' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'constraint' => 1)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('op_log', TRUE, $attributes);
    }

    //企业信息表
    private function table_company(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => 20),
            'intro' => array('type' => 'VARCHAR', 'constraint' => 100),
            'logo' => array('type' => 'VARCHAR', 'constraint' => 100)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('company', TRUE, $attributes);
    }

    //系统配置表
    private function table_setting(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'service_type' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE, 'default' => 0),
            'app_id' => array('type' => 'VARCHAR', 'constraint' => 32),
            'app_key' => array('type' => 'VARCHAR', 'constraint' => 28)
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('setting', TRUE, $attributes);
    }

    //版本信息表
    private function table_version(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'dependent_info' => array('type' => 'VARCHAR', 'constraint' => 20),
            'dependent_code' => array('type' => 'VARCHAR', 'constraint' => 20),
            'version_name' => array('type' => 'VARCHAR', 'constraint' => 10),
            'version_code' => array('type' => 'TINYINT', 'constraint' => 1, 'unsigned' => TRUE),
            'upgrade_content' => array('type' => 'VARCHAR', 'constraint' => 255),
            'upgrade_time' => array('type' => 'DATETIME')
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('version', TRUE, $attributes);
    }

    //验证统计表
    private function table_auth_statistics(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'power_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned'=> TRUE),
            'click_day_auth_count' => array('type' => 'INT', 'constraint' => 11, 'unsigned'=> TRUE, 'default' => 0),
            'hand_day_auth_count' => array('type' => 'INT', 'constraint' => 11, 'unsigned'=> TRUE, 'default' => 0),
            'face_day_auth_count' => array('type' => 'INT', 'constraint' => 11, 'unsigned'=> TRUE, 'default' => 0),
            'noice_day_auth_count' => array('type' => 'INT', 'constraint' => 11, 'unsigned'=> TRUE, 'default' => 0),
            'statistics_time' => array('type' => 'DATETIME')
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('auth_statistics', TRUE, $attributes);
    }

    //用户事件表
    private function table_user_event(){
        $fields = array();
        $fields = array(
            'user_name' => array('type' => 'VARCHAR', 'constraint' => 20),
            'event_id' => array('type' => 'VARCHAR', 'constraint' => 40),
            'power_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE)
        );

        $this->dbforge->add_field($fields);
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('user_event', TRUE, $attributes);
    }

    private function table_session(){
        $fields = array();
        $fields = array(
            'id' => array('type' => 'varchar', 'constraint' => 40),
            'ip_address' => array('type' => 'varchar', 'constraint' => 45),
            'timestamp' => array('type' => 'int', 'constraint' => 10, 'unsigned' => true,  'default' => 0),
            'data' => array('type' => 'blob')
        );

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('timestamp');
        $attributes = array('ENGINE' => 'MyISAM');
        return $this->dbforge->create_table('sessions', TRUE, $attributes);
    }

    public function add_default_data(){

        $need_default_table = array();
        $need_default_table = array(
            'default_group','default_version'
        );

        foreach($need_default_table as $add_data_table){
            $this->$add_data_table();
        }
    }

    private function default_group(){
        $insertData = array();
        $insertData = array(
            'name' => '默认组',
            'inner' => 1
        );

        $this->load->model('webModel/Group_model', 'group');
        return $this->group->insert($insertData);
    }

    private function default_version(){

        $insertData = array();
        $insertData = array(
            array(
                'dependent_info' => '私有云WEB管理系统',
                'dependent_code' => 1,
                'version_name' => '版本1.0',
                'version_code' => 1,
                'upgrade_time' => date('Y-m-d H:i:s'),
                'upgrade_content' => ''
            ),
            array(
                'dependent_info' => '私有云服务组件',
                'dependent_code' => 2,
                'version_name' => '版本1.0',
                'version_code' => 1,
                'upgrade_time' => date('Y-m-d H:i:s'),
                'upgrade_content' => ''
            ),
            array(
                'dependent_info' => '私有云Android SDK',
                'dependent_code' => 3,
                'version_name' => '版本1.0',
                'version_code' => 1,
                'upgrade_time' => date('Y-m-d H:i:s'),
                'upgrade_content' => ''
            ),
            array(
                'dependent_info' => '私有云IOS SDK',
                'dependent_code' => 4,
                'version_name' => '版本1.0',
                'version_code' => 1,
                'upgrade_time' => date('Y-m-d H:i:s'),
                'upgrade_content' => ''
            )
        );
        $this->load->model('webModel/Version_model','version');
        return $this->version->insert($insertData);
    }

    public function check_install(){
        $lock_file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'install.lock';

        if(is_file($lock_file)){
            $this->to_api_message(1, 'has_install');
        }else{
            $this->to_api_message(0, 'need_install');
        }
    }

    public function write_install_file(){
        $lock_file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'install.lock';
        if(!is_file($lock_file)){
            $touch = touch($lock_file);
            if($touch){
                $this->to_api_message(1, 'read_install_file');
            }else{
                $this->to_api_message(1, 'read_install_file');
            }
        }else{
            $this->to_api_message(1, 'read_install_file');
        }
    }
}
