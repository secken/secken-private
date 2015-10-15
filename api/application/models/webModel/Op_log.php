<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Op_log extends CI_Model {

    private $_main_table = 'op_log';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get_count($where){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        if(!empty($where)){
            $this->db->where($where);
        }

        $query = $this->db->get();

        return $query->num_rows();
    }

    public function get_list($where, $limit, $offset){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        if(!empty($where)){
            $this->db->where($where);
        }

        $this->db->limit($limit, $offset);
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
}
