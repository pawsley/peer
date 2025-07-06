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

        if ($data['status_absen'] === 'in') {
            if ($currentTime <= $settokoin) {
                $message = 'absen tepat waktu';
            } else {
                // Calculate late minutes
                $lateMinutes = (strtotime($currentTime) - strtotime($settokoin)) / 60;
                $message = 'absen terlambat ' . round($lateMinutes) . ' menit';
            }
        } elseif ($data['status_absen'] === 'out') {
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

        if ($insert) {
            $this->response([
                'status' => true,
                'message' => $message
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
        $data = [
            'id_user' => $this->post('id_user'),
            'finger_id' => $this->post('finger_id'),
        ];

        if (empty($data['id_user']) || empty($data['finger_id'])) {
            return $this->response([
                'status' => false,
                'message' => 'id_user and finger_id are required'
            ], RestController::HTTP_BAD_REQUEST);
        }

        $insert = $this->api->regist($data);

        if ($insert) {
            $this->response([
                'status' => true,
                'message' => 'Data berhasil disimpan'
            ], RestController::HTTP_CREATED);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Data gagal disimpan'
            ], RestController::HTTP_BAD_REQUEST);
        }
    }
}