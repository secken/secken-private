<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group_model extends CI_Model {

    private $_main_table = 'group_info';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function search($group_name){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->like('name', $group_name);
        
        $query = $this->db->get();

        return $query->result_array();
    }

    public function get_list(){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        $query = $this->db->get();

        return $query->result_array();
    }

    public function get($gid){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where('gid', $gid);

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

    public function delete($where){
        if(empty($where)){
            return 0;
        }

        $this->db->where($where);
        $this->db->delete($this->_main_table);

        return $this->db->affected_rows();
    }
}
