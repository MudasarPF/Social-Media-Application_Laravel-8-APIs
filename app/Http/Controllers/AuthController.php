<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Token;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    function createToken($data)
    {
        $key = "ProgrammersForce";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            'exp' => time() + 3600,
            "data" => $data,
        );

        $jwt = JWT::encode($payload, $key, 'HS256');

        return $jwt;
    }

    function createTempToken($data)
    {
        $key = "ProgrammersForce";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            'exp' => time() + 1000,
            "data" => $data,
        );

        $jwt = JWT::encode($payload, $key, 'HS256');

        return $jwt;
    }

    //Register Action
    public function register(Request $request)
    {
        //Validate the fields
        $fields = $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed'
            ]
        );

        //Create one time token
        $tempToken = $this->createTempToken(time());

        //Create the user
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'remember_token' => $tempToken
        ]);

        //dd($tempToken);

        //Send Email
        $url = url('api/EmailConfirmation/' . $fields['email'] . '/' . $tempToken);

        Mail::to($fields['email'])->send(new ConfirmEmail($url, 'batalew787@ecofreon.com'));

        //return ['status' => 'Confirmation email has been sent. Please check your email'];

        $response = [
            'message' => 'User has been created successfully',
            'user' => $user,
            'Mail response' => 'Email sent succesfully'
        ];

        //Return HTTP 201 status, call was successful and something was created
        return response($response, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();


        if ($user == null) {
            return response([
                'message' => 'User does not exist'
            ]);
        }

        if ($user->email_verified_at == null) {
            return response([
                'message' => 'Your email is not confirmed'
            ]);
        }

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Invalid email or password'
            ], 401);
        }

        //Check if user is already logged in
        $isLoggedIn = Token::where('user_id', $user->id)->first();

        if ($isLoggedIn) {
            return response([
                'message' => 'User already logged-in'
            ], 400);
        }


        $token = $this->createToken($user->id);


        //Store token in database
        $saveToken = Token::create([
            'user_id' => $user->id,
            'token' => $token
        ]);

        $response = [
            'message' => 'Logged in successfully',
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }


    public function logout(Request $request)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));

        //Get Id
        $userId = $decoded->data;

        $userExists = Token::where('user_id', $userId)->first();

        if ($userExists) {
            $userExists->delete();
        } else {
            $message = [
                'message' => 'This user is already logged out'
            ];

            return response($message, 404);
        }

        return [
            'message' => 'Logout Succesfully'
        ];
    }

    public function EmailConfirmation($email , $token)
    {
        $userExists = User::where('email', $email)->first();

        if (!$userExists) {
            return response([
                'message' => 'Something went wrong!'
            ]);
        }

        $userToken = $userExists->remember_token;

        if($userToken != $token)
        {
            return response([
                'message' => 'You are not authorized to use this link'
            ]);
        }

        if ($userExists->email_verified_at != null) {
            return response([
                'message' => 'Your link has expired'
            ]);
        }
        $userExists->email_verified_at = time();
        $userExists->save();
        return response([
            'message' => 'Email Confirmed'
        ]);
    }
}
