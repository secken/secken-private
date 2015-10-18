<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Company extends CI_Model {

    private $_main_table = 'company';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get(){
        $this->db->select('app_id, app_key');
        $this->db->from($this->_main_table);
        $query = $this->db->get();

        return $query->row_array();
    }
}
