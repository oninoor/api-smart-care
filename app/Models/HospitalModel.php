<?php

namespace App\Models;

use CodeIgniter\Model;

class HospitalModel extends Model
{
  protected $table      = "hospitals";
  protected $primaryKey = "id";

  protected $useAutoIncrement = true;

  protected $returnType     = "object";
  protected $useSoftDeletes = true;

  protected $allowedFields = ["id_hospital", "name", "province", "type", "class", "ownership", "email", "password", "client_id", "client_secret", "grant_type", "token", "expired", "base_url", "medical_resume_uri", "medical_resume_detail_uri"];

  protected $useTimestamps = true;
  protected $createdField  = "created_at";
  protected $updatedField  = "updated_at";
  protected $deletedField  = "deleted_at";

  protected $validationRules    = [];
  protected $validationMessages = [];
  protected $skipValidation     = true;
}
