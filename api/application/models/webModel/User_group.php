<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_group extends CI_Model {

    private $_main_table = 'user_group';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get($gid){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where('gid', $gid);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function insert($insertData){
        if(empty($insertData)){
            return 0;
        }

        $this->db->set($insertData);
        $this->db->insert($this->_main_table);

        return $this->db->affected_rows();
    }

    public function update($updateData, $where){
        if(empty($updateData) && empty($where)){
            return 0;
        }

        $this->db->set($updateData);
        $this->db->where_in('user_id',$where);

        $this->db->update($this->_main_table);
        //echo $this->db->last_query();
        return $this->db->affected_rows();
    }

    public function update_group($updateData, $where){
        if(empty($updateData) && empty($where)){
            return 0;
        }

        $this->db->set($updateData);
        $this->db->where($where);

        $this->db->update($this->_main_table);
        //echo $this->db->last_query();
        return $this->db->affected_rows();
    }

    public function delete($user_ids){
        if(empty($user_ids)){
            return 0;
        }

        $this->db->where_in('user_id',$user_ids);
        $this->db->delete($this->_main_table);
        //echo $this->db->last_query();
        return $this->db->affected_rows();
    }
}
