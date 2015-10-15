<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setting_model extends CI_Model {

    private $_main_table = 'setting';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get(){
        $this->db->select('id, service_type, app_id, app_key');
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

    public function update($updateData, $where){
        if(empty($updateData) && empty($where)){
            return 0;
        }

        $this->db->set($updateData);
        $this->db->where($where);

        $this->db->update($this->_main_table);

        return $this->db->affected_rows();
    }
}
