<?php

namespace App\Controllers;

use App\Models\HospitalModel;
use App\Models\TransactionsModel;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

class AccessApi extends BaseController
{
  protected $format    = 'json';

  public function __construct()
  {
    $this->db         = \Config\Database::connect();
    $this->hospitalModel = new HospitalModel();
    $this->transactionsModel = new TransactionsModel();
  }

  public function check_access($id_hospital_req, $nik, $id_hospital_destination)
  {
    if ($id_hospital_destination == null) {
      // Ambil id rs berdasarkan NIK
      $hospitals = $this->get_visit($nik, $id_hospital_req);
      if (!$hospitals) {
        return null;
      }

      // Check token
      foreach ($hospitals as $row) {
        // Cek apakah ada token
        if ($row['token']) {
          // Cek apakah token kadaluarsa
          if (time() >= (strtotime($row['expired']) + 3600)) {
            $this->get_token($row['client_id'], $row['client_secret'], $row['username'], $row['password'], $row['grant_type'], $row['base_url'], $row['id']);
          }
        } else {
          $this->get_token($row['client_id'], $row['client_secret'], $row['username'], $row['password'], $row['grant_type'], $row['base_url'], $row['id']);
        }
      }
    } else {
      $builder = $this->db->table('hospitals');
      $builder->where('id_hospital', $id_hospital_destination);
      $hospital = $builder->get()->getRowArray();

      // Cek apakah ada token
      if ($hospital['token']) {
        // Cek apakah token kadaluarsa
        if (time() >= (strtotime($hospital['expired']) + 3600)) {
          $this->get_token($hospital['client_id'], $hospital['client_secret'], $hospital['username'], $hospital['password'], $hospital['grant_type'], $hospital['base_url'], $hospital['id']);
        }
      } else {
        $this->get_token($hospital['client_id'], $hospital['client_secret'], $hospital['username'], $hospital['password'], $hospital['grant_type'], $hospital['base_url'], $hospital['id']);
      }
    }

    return true;
  }

  public function get_medical_resume($nik, $id_hospital_req, $id_user_req)
  {
    $hospitals = $this->get_visit($nik, $id_hospital_req);
    $data = [];

    // Request resume medis ke setiap rs
    foreach ($hospitals as $row) {
      $data_medical_resume = $this->req_api_medical_resume($row['token'], $row['base_url'], $row['medical_resume_uri'], $id_hospital_req, $row['id_hospital'], $id_user_req, $nik);

      if ($data_medical_resume['status'] == 200) {
        if (isset($data_medical_resume['data'])) {
          $array_resume = $data_medical_resume['data'];
          array_push($data, ['hospital' => $array_resume['hospital'], 'id_hospital' => $array_resume['id_hospital'], 'medical_resume' => $array_resume['medical_resume']]);
          // }
        }
      }
    }
    return $data;
  }

  public function get_medical_resume_detail($id_hospital_req, $id_user_req, $id_hospital_destination, $id_record)
  {
    $builder = $this->db->table('hospitals');
    $builder->where('id_hospital', $id_hospital_destination);
    $hospital = $builder->get()->getRowArray();

    $data_medical_resume_detail = $this->req_api_medical_resume_detail($hospital['token'], $hospital['base_url'], $hospital['medical_resume_detail_uri'], $id_hospital_req, $id_hospital_destination, $id_user_req, $id_record);

    return $data_medical_resume_detail;
  }

  protected function get_visit($nik, $id_hospital_req)
  {
    // Get visits
    $builder = $this->db->table('visits a');
    $builder->select('a.id_hospital, b.id, b.username, b.password, b.client_id, b.client_secret, b.grant_type, b.token, b.expired, b.base_url, b.medical_resume_uri, b.medical_resume_detail_uri, b.updated_at');
    $builder->join('hospitals b', 'a.id_hospital = b.id_hospital');
    $builder->where('a.nik', $nik);
    $builder->where('b.username !=', null);
    $builder->where('b.password !=', null);
    $builder->where('b.client_id !=', null);
    $builder->where('b.client_secret !=', null);
    $builder->where('b.grant_type !=', null);
    $builder->where('b.id_hospital !=', $id_hospital_req);
    $hospitals = $builder->get()->getResultArray();

    // If not exist
    if (!$hospitals) {
      return null;
    }

    return $hospitals;
  }

  protected function get_token($client_id, $client_secret, $username, $password, $grant_type, $base_url, $id) //Get bearer token
  {

    $client = new Client();

    // Set data header for API
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret) . '',
      'Content-Type' => 'application/x-www-form-urlencoded',
    ];

    // Set data x-www-form-urlencoded for API
    $options = [
      'form_params' => [
        'username'    => $username,
        'password'    => $password,
        'grant_type'  => $grant_type
      ],
    ];

    $request = new Request('POST', $base_url . '/login', $headers);
    $response = $client->sendAsync($request, $options)->wait();

    // $code = $response->getStatusCode(); // 200
    $content = json_decode($response->getBody()->getContents(), true); // OK

    if ($content['access_token']) {
      $data = [
        'id'      => $id,
        'token'   => $content['access_token'],
        'expired'   => date('Y-m-d H:i:s', time()),
      ];

      $this->hospitalModel->save($data);
    }

    return $content['access_token'];
  }

  // Resume Medis
  protected function req_api_medical_resume($token, $base_url, $medical_resume_uri, $id_hospital_req, $id_hospital_destination, $id_user_req, $nik)
  {

    try {
      $client = new Client();
      $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/x-www-form-urlencoded'
      ];
      $options = [
        'form_params' => [
          'id_hospital_req' => $id_hospital_req,
          'id_user_req' => $id_user_req,
          'nik' => $nik
        ]
      ];
      $request = new Request('POST', $base_url . '/' . $medical_resume_uri, $headers);
      $res = $client->sendAsync($request, $options)->wait();
      $content = json_decode($res->getBody()->getContents(), true); // OK
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      $response = $e->getResponse();
      $code = $response->getStatusCode(); //Ambil kode

      $body = json_decode($response->getBody()->getContents(), true);

      // Simpan data transaksi gagal
      $data = [
        'id_hospital_req'         => $id_hospital_req,
        'id_user_req'             => $id_user_req,
        'id_hospital_destination' => $id_hospital_destination,
        'status'                  => 0,
        'description'             => $code . ' - ' . $body['message'],
        'url'                     => $base_url . '/' . $medical_resume_uri,
      ];
      $this->transactionsModel->save($data);

      return $body;
    }

    // Simpan data transaksi berhasil
    $data = [
      'id_hospital_req'         => $id_hospital_req,
      'id_user_req'             => $id_user_req,
      'id_hospital_destination' => $id_hospital_destination,
      'status'                  => 1,
      'description'             => 'Success',
      'url'                     => $base_url . '/' . $medical_resume_uri,
    ];
    $this->transactionsModel->save($data);

    return $content;
  }

  // Detail Resume
  protected function req_api_medical_resume_detail($token, $base_url, $medical_resume_detail_uri, $id_hospital_req, $id_hospital_destination, $id_user_req, $id_record)
  {

    try {
      $client = new Client();
      $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/x-www-form-urlencoded'
      ];
      $options = [
        'form_params' => [
          'id_hospital_req' => $id_hospital_req,
          'id_user_req' => $id_user_req,
          'id_record' => $id_record
        ]
      ];
      $request = new Request('POST', $base_url . '/' . $medical_resume_detail_uri, $headers);
      $res = $client->sendAsync($request, $options)->wait();
      $content = json_decode($res->getBody()->getContents(), true); // OK

    } catch (\GuzzleHttp\Exception\RequestException $e) {
      $response = $e->getResponse();
      $code = $response->getStatusCode(); //Ambil kode
      $body = json_decode($response->getBody()->getContents(), true);

      // Simpan data transaksi gagal
      $data = [
        'id_hospital_req'         => $id_hospital_req,
        'id_user_req'             => $id_user_req,
        'id_hospital_destination' => $id_hospital_destination,
        'status'                  => 0,
        'description'             => $code . ' - ' . $body['message'],
        'url'                     => $base_url . '/' . $medical_resume_detail_uri,
      ];
      $this->transactionsModel->save($data);

      return $body;
    }

    // Simpan data transaksi berhasil
    $data = [
      'id_hospital_req'         => $id_hospital_req,
      'id_user_req'             => $id_user_req,
      'id_hospital_destination' => $id_hospital_destination,
      'status'                  => 1,
      'description'             => 'Success',
      'url'                     => $base_url . '/' . $medical_resume_detail_uri,
    ];
    $this->transactionsModel->save($data);

    return $content;
  }
}
