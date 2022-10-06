<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Home extends ResourceController
{
  protected $format    = 'json';

  public function index()
  {
    $respond = [
      'status'    => 200,
      'error'     => null,
      'messages'  => [
        'success'   => 'Ready to connect'
      ],
      'data'      => null,
    ];

    return $this->respond($respond, 200);
  }
}
