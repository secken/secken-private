<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_log extends CI_Model {

    private $_main_table = 'auth_log';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get_list($where, $limit = 0, $offset = 0){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        if(!empty($where)){
            $this->db->where($where);
        }

        if($limit > 0){
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();

        return $query->result_array();
    }

    public function get_list_for_stats($where){
        $this->db->select('power_id, count(*) as day_count');
        $this->db->from($this->_main_table);

        if(!empty($where)){
            $this->db->where($where);
        }

        $this->db->group_by('power_id');
        $query = $this->db->get();

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

    public function update_event_result($event_id, $auth_result){

        $this->db->set('auth_result', $auth_result);
        $this->db->where('event_id', $event_id);
        $this->db->update($this->_main_table);

        return $this->db->affected_rows();
    }

}
