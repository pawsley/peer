<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {
  public function is_valid_token($token) {
    return $this->db->get_where('tb_token', ['token' => $token])->row();
  }
  public function maxregist($token) {
    return $this->db->get_where('tb_token', ['token' => $token]);
  }
  public function count_finger_id() {
      return $this->db->from('tb_finger')->count_all_results();
  }
  public function regist($data) {
    $insert = $this->db->insert('tb_finger', [
      'finger_id' => $data['finger_id'],
    ]);
    return $insert;
  }
  public function absen($data) {
    $insert = $this->db->insert('tb_finger_absen', [
      'finger_id' => $data['finger_id'],
      'status_absen' => $data['status_absen'],
      'absen_at' => $data['absen_at'],
    ]);
    return $insert;
  }
  public function rest($data) {
    $insert = $this->db->insert('tb_finger_absen', [
      'finger_id' => $data['finger_id'],
      'status_rest' => $data['status_rest'],
      'rest_at' => $data['rest_at'],
    ]);
    return $insert;
  }
}

/* End of file Api_model.php */
/* Location: ./application/models/Api_model.php */