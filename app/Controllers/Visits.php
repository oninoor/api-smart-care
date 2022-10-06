<?php

namespace App\Controllers;

use App\Models\VisitModel;
use CodeIgniter\RESTful\ResourceController;

class Visits extends ResourceController
{
    // protected $modelName = 'App\Models\Hospital';
    protected $format    = 'json';

    public function __construct()
    {
        $this->visitModel = new VisitModel();
        $this->db         = \Config\Database::connect();
    }

    public function add_visits()
    {
        // Rules
        $rules = [
            'id_hospital'   => 'required|numeric',
            'id_user_req'   => 'required',
            'nik'           => 'required|numeric|exact_length[16]',
        ];

        // Validate
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $id_hospital = $this->request->getVar('id_hospital');
        $nik = $this->request->getVar('nik');

        // Check if data exist
        $builder = $this->db->table('visits');
        $builder->where('nik', $nik);
        $builder->where('id_hospital', $id_hospital);
        $patient = $builder->get()->getResult();

        if ($patient) {
            $respond = [
                'status'    => 409,
                'error'     => 409,
                'message'   => 'Error : Data already exist!',
                'data'      => null
            ];
            return $this->respond($respond, 409);
        }

        // Get all data send by POST
        $data = $this->request->getVar();

        // Try to insert into visit tables
        if (!$this->visitModel->save($data)) {
            return $this->fail($this->model->errors());
        }

        // Return Success
        $respond = [
            'status'    => 201,
            'error'     => null,
            'messages'  => 'Success : Data has ben saved!',
            'data'      => $data
        ];
        return $this->respond($respond, 201);
    }
}
