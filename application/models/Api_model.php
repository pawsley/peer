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
    $insert = $this->db->insert('tb_finger_rest', [
      'finger_id' => $data['finger_id'],
      'status_rest' => $data['status_rest'],
      'rest_at' => $data['rest_at'],
    ]);
    return $insert;
  }
  public function delete_finger($finger_id) {
    $this->db->where('finger_id', $finger_id);
    return $this->db->delete('tb_finger');
  }
  public function get_finger_with_last_rest($finger_id, $date)
  {
    $date_start = date('Y-m-d 00:00:00', strtotime($date));
    $date_end   = date('Y-m-d 23:59:59', strtotime($date));

    $this->db->select('f.nama_lengkap, f.shift, f.shift_in, r.status_rest AS last_status');
    $this->db->from('vfingerdata f');
    $this->db->join(
          '(SELECT t1.finger_id, t1.status_rest
            FROM tb_finger_rest t1
            INNER JOIN (
                SELECT finger_id, MAX(id) AS max_id
                FROM tb_finger_rest
                WHERE rest_at >= "'.$date_start.'"
                  AND rest_at <= "'.$date_end.'"
                GROUP BY finger_id
            ) t2 ON t1.finger_id = t2.finger_id AND t1.id = t2.max_id
          ) r',
          'r.finger_id = f.finger_id',
          'left'
      );
    $this->db->where('f.finger_id', $finger_id);
    
    return $this->db->get()->row();
  }
  public function get_finger_with_last_absen($finger_id, $date)
  {
      $date_start = date('Y-m-d 00:00:00', strtotime($date));
      $date_end   = date('Y-m-d 23:59:59', strtotime($date));

      $this->db->select('f.nama_lengkap, f.shift, f.shift_in, r.status_absen AS last_status');
      $this->db->from('vfingerdata f');
      $this->db->join(
          '(SELECT t1.finger_id, t1.status_absen
            FROM tb_finger_absen t1
            INNER JOIN (
                SELECT finger_id, MAX(id) AS max_id
                FROM tb_finger_absen
                WHERE absen_at >= "'.$date_start.'"
                  AND absen_at <= "'.$date_end.'"
                GROUP BY finger_id
            ) t2 ON t1.finger_id = t2.finger_id AND t1.id = t2.max_id
          ) r',
          'r.finger_id = f.finger_id',
          'left'
      );
      $this->db->where('f.finger_id', $finger_id);

      return $this->db->get()->row();
  }
}

/* End of file Api_model.php */
/* Location: ./application/models/Api_model.php */