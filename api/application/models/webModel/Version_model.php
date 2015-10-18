<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Version_model extends CI_Model {

    private $_main_table = 'version';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get_list(){

        $this->db->select('*');
        $this->db->from($this->_main_table);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insert($insertData){
        $this->db->insert_batch($this->_main_table, $insertData);
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
