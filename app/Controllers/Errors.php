<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Errors extends ResourceController
{
    use ResponseTrait;
    public function forbidden()
    {
        // Forbidden action
        return $this->fail("Forbidden Access");
    }
}
