<?php
namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class service{

    /*
        Function to create a JWT token
        parameter: user_id
    */
    public function createToken($data)
    {
        $key = config('constants.JWT_KEY');
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            'exp' => time() + 3600,
            "data" => $data,
        );

        $jwt = JWT::encode($payload, $key, config('constants.JWT_ALGORITHM'));

        return $jwt;
    }

    /*
        Function to create a temporary JWT token that is used to
        verify user's account
        parameter: time()
    */
    public function createTempToken($data)
    {
        $key = config('constants.JWT_KEY');
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            'exp' => time() + 1000,
            "data" => $data,
        );

        $jwt = JWT::encode($payload, $key, config('constants.JWT_ALGORITHM'));

        return $jwt;
    }

}