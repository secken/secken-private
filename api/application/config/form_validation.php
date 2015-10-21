<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    $config = array(
        'Index/add_database_config_file' => array(
            array(
                'field' => 'host_name',
                'label' => 'lang:host_name',
                'rules' => 'trim|valid_ip',
            ),
            array(
                'field' => 'db_name',
                'label' => 'lang:db_name',
                'rules' => 'trim|alpha_dash',
            ),
            array(
                'field' => 'db_user',
                'label' => 'lang:db_user',
                'rules' => 'trim|alpha',
            ),
            array(
                'field' => 'db_pre',
                'label' => 'lang:db_pre',
                'rules' => 'trim|alpha',
            ),
        ),
        'Group/add' => array(
            array(
                'field' => 'group_name',
                'label' => 'lang:group_name',
                'rules' => 'trim|required|max_length[5]'
            )
        ),
        'Group/edit' => array(
            array(
                'field' => 'gid',
                'label' => 'lang:gid',
                'rules' => 'trim|required|integer'
            ),
            array(
                'field' => 'group_name',
                'label' => 'lang:group_name',
                'rules' => 'trim|required|max_length[5]'
            )
        ),
        'Group/delete' => array(
            array(
                'field' => 'gid',
                'label' => 'lang:gid',
                'rules' => 'trim|required|integer'
            ),
        ),
        'Group/set_power' => array(
            array(
                'field' => 'gid',
                'label' => 'lang:gid',
                'rules' => 'trim|required|integer'
            ),
            array(
                'field' => 'set',
                'label' => 'lang:set',
                'rules' => 'trim|required|integer|in_list[0,1]'
            ),
            array(
                'field' => 'power_id',
                'label' => 'lang:power_id',
                'rules' => 'trim|required|integer'
            )
        ),
        'Power/add' => array(
            array(
                'field' => 'power_name',
                'label' => 'lang:power_name',
                'rules' => 'trim|required|max_length[10]'
            ),
            array(
                'field' => 'power_intro',
                'label' => 'lang:power_intro',
                'rules' => 'trim|required|max_length[30]'
            )
        ),
        'Power/power_switch' => array(
            array(
                'field' => 'power_id',
                'label' => 'lang:power_id',
                'rules' => 'trim|required|integer'
            )
        ),
        'Power/delete' => array(
            array(
                'field' => 'power_id',
                'label' => 'lang:power_id',
                'rules' => 'trim|required|integer'
            )
        ),
        'Power/edit' => array(
            array(
                'field' => 'power_id',
                'label' => 'lang:power_id',
                'rules' => 'trim|required|integer'
            ),
            array(
                'field' => 'power_name',
                'label' => 'lang:power_name',
                'rules' => 'trim|required|max_length[10]'
            ),
            array(
                'field' => 'power_intro',
                'label' => 'lang:power_intro',
                'rules' => 'trim|required|max_length[30]'
            ),
            array(
                'field' => 'power_status',
                'label' => 'lang:power_status',
                'rules' => 'trim|required|integer|in_list[0,1]'
            )
        ),
        'Power/regen_auth_key' => array(
            array(
                'field' => 'id',
                'label' => 'lang:power_id',
                'rules' => 'trim|required|integer'
            )
        ),
        'Setting/set_service' => array(
            array(
                'field' => 'app_id',
                'label' => 'lang:app_id',
                'rules' => 'trim|required|alpha_numeric|exact_length[32]'
            ),
            array(
                'field' => 'app_key',
                'label' => 'lang:app_key',
                'rules' => 'trim|required|alpha_numeric|exact_length[20]'
            )
        ),
        'Setting/get_event_result' => array(
            array(
                'field' => 'event_id',
                'label' => 'lang:event_id',
                'rules' => 'trim|required'
            )
        ),
        'Company/set' => array(
            array(
                'field' => 'company_name',
                'label' => 'lang:company_name',
                'rules' => 'trim|required|max_length[20]'
            ),
            array(
                'field' => 'company_intro',
                'label' => 'lang:company_intro',
                'rules' => 'trim|max_length[100]'
            )
        ),
        'User/add' => array(
            array(
                'field' => 'gid',
                'label' => 'lang:gid',
                'rules' => 'trim|required|integer'
            ),
            array(
                'field' => 'user_name',
                'label' => 'lang:user_name',
                'rules' => 'trim|required|max_length[50]'
            ),
            array(
                'field' => 'true_name',
                'label' => 'lang:true_name',
                'rules' => 'trim|required|max_length[20]'
            ),
            array(
                'field' => 'phone',
                'label' => 'lang:phone',
                'rules' => 'trim|required|numeric|exact_length[11]'
            ),
            array(
                'field' => 'intro',
                'label' => 'lang:intro',
                'rules' => 'trim|max_length[50]'
            ),
            array(
                'field' => 'is_admin',
                'label' => 'lang:is_admin',
                'rules' => 'trim|integer|in_list[0,1]'
            )
        ),
        'User/edit' => array(
            array(
                'field' => 'true_name',
                'label' => 'lang:true_name',
                'rules' => 'trim|required|max_length[20]'
            ),
            array(
                'field' => 'open',
                'label' => 'lang:open',
                'rules' => 'trim|required|integer|in_list[0,1]'
            ),
            array(
                'field' => 'uid',
                'label' => 'lang:uid',
                'rules' => 'trim|required|integer'
            ),
        ),
        'User/delete' => array(
            array(
                'field' => 'uids',
                'label' => 'lang:uids',
                'rules' => 'trim|required'
            ),
        ),
        'User/move_to_anthor_group' => array(
            array(
                'field' => 'uids',
                'label' => 'lang:uids',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'gid',
                'label' => 'lang:gid',
                'rules' => 'trim|required|integer'
            ),
        )
    );
?>
