<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

    private $_main_table = 'user';
    private $_group_table = 'user_group';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get_count($group_id){
        $this->db->select('*');
        $this->db->from($this->_group_table);
        $this->db->where('gid', $group_id);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_search_count($wd, $gid, $is_phone = false){
        $this->db->select('*');
        $this->db->from($this->_main_table .' AS u');
        $this->db->join($this->_group_table . ' AS g', 'g.user_id = u.user_id','left');
        if($is_phone){
            $this->db->like('u.phone', $wd);
        }else{
            $this->db->like('u.true_name', $wd);
        }

        $query = $this->db->get();

        return $query->num_rows();
    }

    public function get_user_by_id($user_id){

        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where_in('user_id', $user_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_admin_user(){
        $this->db->select('user_id, user_name, phone, create_time');
        $this->db->from($this->_main_table);
        $this->db->where('is_admin', 1);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get($group_id, $limit = null, $offset=0){
        $this->db->select('*');
        $this->db->from($this->_main_table . ' AS u');
        $this->db->join($this->_group_table . ' AS ug', 'ug.user_id = u.user_id', 'left');
        $this->db->where('ug.gid', $group_id);

        if(!is_null($limit) && $limit >=0){
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result_array();
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

    public function delete($user_ids){
        if(empty($user_ids)){
            return 0;
        }

        $this->db->where_in('user_id', $user_ids);
        $this->db->delete($this->_main_table);

        return $this->db->affected_rows();
    }

    public function search($wd, $gid, $limit, $offset, $is_phone = false){
        $this->db->select('*');
        $this->db->from($this->_main_table .' AS u');
        $this->db->join($this->_group_table . ' AS g', 'g.user_id = u.user_id','left');
        if($is_phone){
            $this->db->like('u.phone', $wd);
        }else{
            $this->db->like('u.true_name', $wd);
        }

        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        return $query->result_array();
    }

    public function check_user_name($user_name){

        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where('user_name', $user_name);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function check_phone($phone){

        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where('phone', $phone);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_by_where($where){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where($where);

        $query = $this->db->get();
        return $query->result_array();
    }
}
