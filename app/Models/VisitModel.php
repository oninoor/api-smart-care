<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
  protected $table      = "visits";
  protected $primaryKey = "id";

  protected $useAutoIncrement = true;

  protected $returnType     = "object";
  protected $useSoftDeletes = true;

  protected $allowedFields = ["id_hospital", "nik", "id_user_req"];

  protected $useTimestamps = true;
  protected $createdField  = "created_at";
  protected $updatedField  = "updated_at";
  protected $deletedField  = "deleted_at";

  protected $validationRules    = [];
  protected $validationMessages = [];
  protected $skipValidation     = true;
}
