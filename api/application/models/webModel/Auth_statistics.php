<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_statistics extends CI_Model {

    private $_main_table = 'auth_statistics';

    public function __construct(){
        parent::__construct();

        $this->load->database();
    }

    public function get_auth_statistics(){
        $this->db->select('
            SUM(click_day_auth_count) as click_sum,
            SUM(hand_day_auth_count) as hand_sum,
            SUM(face_day_auth_count) as face_sum,
            SUM(noice_day_auth_count) as noicd_sum
        ');

        $this->db->from($this->_main_table);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_ty_time($start, $end){

        $this->db->select('
            SUM(click_day_auth_count) as click_sum,
            SUM(hand_day_auth_count) as hand_sum,
            SUM(face_day_auth_count) as face_sum,
            SUM(noice_day_auth_count) as noicd_sum
        ');

        $this->db->from($this->_main_table);

        $this->db->where('statistics_time >=', $start);
        $this->db->where('statistics_time <=', $end);

        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_count($power_id){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        $this->db->where('power_id', $power_id);

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_by_power($power_id, $limit, $offset){
        $this->db->select('*');
        $this->db->from($this->_main_table);

        $this->db->where('power_id', $power_id);
        $this->db->limit($limit, $offset);
        $this->db->order_by('statistics_time', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insert($insertData){
        $this->db->insert($this->_main_table, $insertData);
        return $this->db->insert_id();
    }

}
