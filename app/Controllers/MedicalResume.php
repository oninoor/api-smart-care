<?php

namespace App\Controllers;

use App\Models\VisitModel;
use CodeIgniter\RESTful\ResourceController;

class MedicalResume extends ResourceController
{
    // protected $modelName = 'App\Models\Hospital';
    protected $format    = 'json';

    public function __construct()
    {
        $this->visitModel = new VisitModel();
        $this->db         = \Config\Database::connect();
        $this->accessApi = new AccessApi();
    }

    public function index()
    {
        // Rules
        $rules = [
            'id_hospital_req'   => 'required',
            'id_user_req'       => 'required',
            'nik'               => 'required|numeric|exact_length[16]',
        ];

        // Validate
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $id_hospital_req    = $this->request->getVar('id_hospital_req');
        $id_user_req        = $this->request->getVar('id_user_req');
        $nik                = $this->request->getVar('nik');

        // Cek apakah token dari masing masing api rumah sakit sudah kadaluarsa
        $check_access = $this->accessApi->check_access($id_hospital_req, $nik, null);

        if (!$check_access) {
            // Return Error
            $respond = [
                'status'    => 404,
                'error'     => 404,
                'message'   => 'Error : Data Not Found!',
                'data'      => null
            ];
            return $this->respond($respond, 404);
        }

        // Ambil data resume medis dari rumah sakit yang tersedia
        $medical_resume = $this->accessApi->get_medical_resume($nik, $id_hospital_req, $id_user_req);


        $respond = [
            'status'    => 200,
            'error'     => null,
            'message'  => 'Success',
            'data'      => $medical_resume
        ];
        return $this->respond($respond, 200);
    }

    public function medical_resume_detail()
    {
        // Rules
        $rules = [
            'id_hospital_req'           => 'required',
            'id_hospital_destination'   => 'required',
            'id_user_req'               => 'required',
            'id_record'                 => 'required',
        ];

        // Validate
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $id_hospital_req            = $this->request->getVar('id_hospital_req');
        $id_user_req                = $this->request->getVar('id_user_req');
        $id_hospital_destination    = $this->request->getVar('id_hospital_destination');
        $id_record                  = $this->request->getVar('id_record');

        // Cek apakah token dari masing masing api rumah sakit sudah kadaluarsa
        $check_access = $this->accessApi->check_access($id_hospital_req, null, $id_hospital_destination);

        if (!$check_access) {
            // Return Error
            $respond = [
                'status'    => 404,
                'error'     => 404,
                'message'   => 'Error : Data Not Found!',
                'data'      => null
            ];
            return $this->respond($respond, 404);
        }

        // Ambil data resume medis dari rumah sakit yang tersedia
        $medical_resume = $this->accessApi->get_medical_resume_detail($id_hospital_req, $id_user_req, $id_hospital_destination, $id_record);

        return $this->respond($medical_resume, 200);
    }
}
