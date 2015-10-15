<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Model {

    private $_main_table = 'Test';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function insert($insertData){
        if(empty($insertData)){
            return 0;
        }

        $this->db->set($insertData);
        $this->db->insert($this->_main_table);

        return $this->db->insert_id();
    }

    public function get($event_id){
        $this->db->select('*');
        $this->db->from($this->_main_table);
        $this->db->where('event_id', $event_id);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function delete($event_id){
        $this->db->where('event_id', $event_id);
        $this->db->delete($this->_main_table);
    }

}
