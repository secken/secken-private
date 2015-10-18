<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Power_model extends CI_Model {

    private $_main_table = 'power';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get($power_id){
        $this->db->select('id, name, power_key');
        $this->db->from($this->_main_table);
        $this->db->where('power_id', $power_id);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function get_by_power_id($power_id){
        $this->db->select('id');
        $this->db->from($this->_main_table);
        $this->db->where('power_id', $power_id);
        $query = $this->db->get();

        return $query->row_array();
    }
}
