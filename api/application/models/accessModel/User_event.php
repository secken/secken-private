<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_event extends CI_Model {

    private $_main_table = 'user_event';

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function get($event_id){
        $this->db->select('user_name, power_id');
        $this->db->from($this->_main_table);
        $this->db->where('event_id', $event_id);

        $query = $this->db->get();

        return $query->row_array();
    }

    public function insert($insertData){
        $this->db->set($insertData);
        $this->db->insert($this->_main_table);
        return $this->db->insert_id();
    }
}
