<?php
defined('BASEPATH') or exit('No direct script access allowed');
use chriskacerguis\RestServer\RestController;

class Api extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model', 'api');
        $this->_check_token();
    }

    private function _check_token()
    {
        $headers = $this->input->request_headers();
        if (!isset($headers['Authorization'])) {
            $this->response([
                'status' => false,
                'message' => 'Unauthorized: Missing token'
            ], RestController::HTTP_UNAUTHORIZED);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (!$this->api->is_valid_token($token)) {
            $this->response([
                'status' => false,
                'message' => 'Unauthorized: Invalid or expired token'
            ], RestController::HTTP_UNAUTHORIZED);
            exit;
        }
    }

    public function check_token_get()
    {
      $headers = $this->input->request_headers();

      if (!isset($headers['Authorization'])) {
          return $this->response([
              'status' => false,
              'message' => 'Authorization token missing'
          ], RestController::HTTP_UNAUTHORIZED);
      }

      $token = str_replace('Bearer ', '', $headers['Authorization']);

      $is_valid = $this->api->is_valid_token($token);

      if ($is_valid) {
          return $this->response([
              'status' => true,
              'message' => 'Token is valid'
          ], RestController::HTTP_OK);
      } else {
          return $this->response([
              'status' => false,
              'message' => 'Invalid token'
          ], RestController::HTTP_UNAUTHORIZED);
      }
    }
    public function absen_post()
    {
        $settokoin = '09:00:00';
        $settokout = '17:00:00';

        $data = [
            'finger_id'    => $this->post('finger_id'),
            'status_absen' => $this->post('status_absen'),
            'absen_at'     => date('Y-m-d H:i:s'),
        ];

        if (empty($data['finger_id']) || empty($data['status_absen'])) {
            return $this->response([
                'status' => false,
                'message' => 'finger_id and status_absen are required'
            ], RestController::HTTP_BAD_REQUEST);
        }

        $currentTime = date('H:i:s');
        $message = '';

        if ($data['status_absen'] === 'IN') {
            if ($currentTime <= $settokoin) {
                $message = 'absen tepat waktu';
            } else {
                // Calculate late minutes
                $lateMinutes = (strtotime($currentTime) - strtotime($settokoin)) / 60;
                $message = 'absen terlambat ' . round($lateMinutes) . ' menit';
            }
        } elseif ($data['status_absen'] === 'OUT') {
            if ($currentTime >= $settokout) {
                $message = 'pulang tepat waktu';
            } else {
                $earlyMinutes = (strtotime($settokout) - strtotime($currentTime)) / 60;
                $message = 'pulang lebih awal ' . round($earlyMinutes) . ' menit';
            }
        } else {
            $message = 'status_absen tidak dikenal';
        }

        $insert = $this->api->absen($data);
        $nama = $this->db->get_where('vfingerdata', ['finger_id' => $data['finger_id']])->row();

        if ($insert) {
            $this->response([
                'status' => true,
                'message' => $message,
                'nama' => $nama->nama_lengkap.' berhasil absen masuk ',
            ], RestController::HTTP_CREATED);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Gagal absen'
            ], RestController::HTTP_BAD_REQUEST);
        }
    }
    public function register_post()
    {
        // Get headers and extract token
        $headers = $this->input->request_headers();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

        if (!$token) {
            return $this->response([
                'status' => false,
                'message' => 'Authorization token is missing'
            ], RestController::HTTP_UNAUTHORIZED);
        }

        // Get user/token data
        $user = $this->api->maxregist($token)->row();

        if (!$user) {
            return $this->response([
                'status' => false,
                'message' => 'Invalid token'
            ], RestController::HTTP_UNAUTHORIZED);
        }

        // Registration limit from token
        $max_limit = $user->max_limit;

        // Count all existing registrations
        $current_count = $this->api->count_finger_id();

        if ($current_count >= $max_limit) {
            return $this->response([
                'status' => false,
                'message' => 'Maximum registrations reached'
            ], RestController::HTTP_BAD_REQUEST);
        }

        // Get finger_id from POST data
        $finger_id = $this->post('finger_id');

        if (empty($finger_id)) {
            return $this->response([
                'status' => false,
                'message' => 'finger_id is required'
            ], RestController::HTTP_BAD_REQUEST);
        }

        // Prepare data for insertion
        $data = [
            'finger_id' => $finger_id,
            'regist_at' => date('Y-m-d H:i:s'),
        ];

        // Save to database
        $insert = $this->api->regist($data);

        if ($insert) {
            return $this->response([
                'status' => true,
                'message' => 'Data berhasil disimpan'
            ], RestController::HTTP_CREATED);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Data gagal disimpan'
            ], RestController::HTTP_BAD_REQUEST);
        }
    }
    public function delete_post(){
        
        $finger_id = $this->post('finger_id');

        if (empty($finger_id)) {
            return $this->response([
                'status' => false,
                'message' => 'finger_id is required'
            ], RestController::HTTP_BAD_REQUEST);
        }

        $delete = $this->api->delete_finger($finger_id);

        if ($delete) {
            return $this->response([
                'status' => true,
                'message' => 'Data berhasil dihapus',
                'name' => $finger_id.' berhasil dihapus'
            ], RestController::HTTP_OK);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ], RestController::HTTP_BAD_REQUEST);
        }
    }
}