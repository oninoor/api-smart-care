<?php

namespace App\Controllers;

use \App\Libraries\Oauth;
use \OAuth2\Request;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Auth extends BaseController
{
    use ResponseTrait;

    // Membuat Login untuk user
    public function login()
    {
        $oauth = new Oauth();
        $request = new Request();
        $respond = $oauth->server->handleTokenRequest($request->createFromGlobals());
        $code = $respond->getStatusCode();
        $body = $respond->getResponseBody();

        return $this->respond(json_decode($body), $code);
    }
}
