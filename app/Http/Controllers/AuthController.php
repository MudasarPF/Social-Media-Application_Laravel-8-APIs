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
use Throwable;

use App\Helpers;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\service;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /*
        Function to create a JWT token
        parameter: user_id
    */
    // function createToken($data)
    // {
    //     $key = config('constants.JWT_KEY');
    //     $payload = array(
    //         "iss" => "http://127.0.0.1:8000",
    //         "aud" => "http://127.0.0.1:8000/api",
    //         "iat" => time(),
    //         "nbf" => 1357000000,
    //         'exp' => time() + 3600,
    //         "data" => $data,
    //     );

    //     $jwt = JWT::encode($payload, $key, config('constants.JWT_ALGORITHM'));

    //     return $jwt;
    // }

    /*
        Function to create a temporary JWT token that is used to
        verify user's account
        parameter: time()
    */
    // function createTempToken($data)
    // {
    //     $key = config('constants.JWT_KEY');
    //     $payload = array(
    //         "iss" => "http://127.0.0.1:8000",
    //         "aud" => "http://127.0.0.1:8000/api",
    //         "iat" => time(),
    //         "nbf" => 1357000000,
    //         'exp' => time() + 1000,
    //         "data" => $data,
    //     );

    //     $jwt = JWT::encode($payload, $key, config('constants.JWT_ALGORITHM'));

    //     return $jwt;
    // }

    /*
        Function to create a new user
    */
    public function register(RegisterRequest $request)
    {
        try {
            //Validate the fields
            $fields = $request->validated();

            //Create one time token
            $tempToken = (new service)->createTempToken(time());

            //Create the user
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'remember_token' => $tempToken
            ]);


            //Send Email
            $url = url('api/EmailConfirmation/' . $fields['email'] . '/' . $tempToken);

            Mail::to($fields['email'])->send(new ConfirmEmail($url, config('constants.MAILTRAP_SENDER_EMAIL_ADDRESS')));

            $response = [
                'message' => 'User has been created successfully',
                'user' => $user,
                'Mail response' => 'Email sent succesfully'
            ];

            //Return HTTP 201 status, call was successful and something was created
            return response($response, 201);
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to login the user by assigning a token to it
        parameter: user_id
    */
    public function login(LoginRequest $request)
    {
        try {
            $fields = $request->validated();

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
                    'message' => 'Invalid credentials'
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
                'Message' => 'Logged in successfully',
                'User' => new AuthResource($user),
                'Token' => $token
            ];

            return response($response, 201);
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to logout the user by deleting its JWT token
    */
    public function logout(Request $request)
    {
        try {
            //Get Bearer Token
            $getUserId = getUserId($request);

            $userExists = Token::where('user_id', $getUserId)->first();

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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }



    /*
        Function to confirm the user registration by email
        this function can be hit through the link sent to the user
        to their email address
        
        parameter: user_id
    */
    public function EmailConfirmation($email, $token)
    {
        try {
            $userExists = User::where('email', $email)->first();

            if (!$userExists) {
                return response([
                    'message' => 'Something went wrong!'
                ]);
            }

            $userToken = $userExists->remember_token;

            if ($userToken != $token) {
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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }
}
