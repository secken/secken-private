<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Device_statistics extends CI_Model {

    private $_main_table = 'device_statistics';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function device_distribution(){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        $query = $this->db->get();
        return $query->result_array();
    }

}
