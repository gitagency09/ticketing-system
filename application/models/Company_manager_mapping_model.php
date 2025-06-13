<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_manager_mapping_model extends CI_Model {

    protected $table = 'company_manager_mapping';

    public function __construct() {
        parent::__construct();
    }

    // Get all mappings
    public function get_all() {
        return $this->db->get($this->table)->result_array();
    }

    // Get mappings by user ID
    public function get_by_user($user_id) {
        return $this->db->get_where($this->table, ['user_id' => $user_id])->result_array();
    }

    // Get mappings by company ID
    public function get_by_company($company_id) {
        return $this->db->get_where($this->table, ['company_id' => $company_id])->result_array();
    }

    // Insert new mapping
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Delete mapping by ID
    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    // Delete mapping by company ID and user ID
    public function delete_by_company_user($company_id, $user_id) {
        return $this->db->delete($this->table, ['company_id' => $company_id, 'user_id' => $user_id]);
    }

    // Check if mapping exists
    public function exists($company_id, $user_id) {
        return $this->db->get_where($this->table, ['company_id' => $company_id, 'user_id' => $user_id])->num_rows() > 0;
    }

    public function get_company_ids_by_user($user_id) {
        $this->db->select('company_id');
        $this->db->from($this->table);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();

        $result = $query->result_array();
        return array_column($result, 'company_id');
    }
}
