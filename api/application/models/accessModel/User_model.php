<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

    private $_main_table = 'user';
    private $_user_group_table = 'user_group';

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function get_user_group($username){
        $this->db->select('g.gid');
        $this->db->from($this->_main_table . ' AS u');
        $this->db->join($this->_user_group_table . ' AS g', 'g.user_id = u.user_id', 'LEFT');
        $this->db->where('u.user_name', $username);

        $query = $this->db->get();

        return $query->result_array();
    }

    public function get_yangcong_uid($user_name){
        $this->db->select('yangcong_uid');
        $this->db->from($this->_main_table);
        $this->db->where('user_name', $user_name);

        $query = $this->db->get();
        return $query->row_array();
    }
}
