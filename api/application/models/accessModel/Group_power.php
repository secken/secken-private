<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group_power extends CI_Model {

    private $_main_table = 'group_power';
    private $_power_table = 'power';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get($gid){
        $this->db->select('p.id');
        $this->db->from($this->_main_table . ' AS gp');
        $this->db->join($this->_power_table . ' AS p','gp.power_id = p.id','left');
        $this->db->where_in('gp.gid', $gid);
        $this->db->where('p.status', 1);

        $query = $this->db->get();
        return $query->result_array();
    }
}
