<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionsModel extends Model
{
  protected $table      = "transactions";
  protected $primaryKey = "id";

  protected $useAutoIncrement = true;

  protected $returnType     = "array";
  protected $useSoftDeletes = true;

  protected $allowedFields = ["id_hospital_req", "id_user_req", "id_hospital_destination", "status", "description", "url"];

  protected $useTimestamps = true;
  protected $createdField  = "created_at";
  protected $updatedField  = "updated_at";
  protected $deletedField  = "deleted_at";

  protected $validationRules    = [];
  protected $validationMessages = [];
  protected $skipValidation     = true;
}
