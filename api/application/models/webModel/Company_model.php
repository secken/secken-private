<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Company_model extends CI_Model {

    private $_main_table = 'company';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get(){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $query = $this->db->get();

        return $query->row_array();
    }

    public function insert($insertData){
        if(empty($insertData)){
            return 0;
        }

        $this->db->set($insertData);
        $this->db->insert($this->_main_table);

        return $this->db->insert_id();
    }

    public function update($updateData){

        if(empty($updateData)){
            return 0;
        }

        $this->db->set($updateData);
        $this->db->where('id', 1);

        $this->db->update($this->_main_table);

        return $this->db->affected_rows();
    }
}
