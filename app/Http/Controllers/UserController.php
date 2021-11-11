<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
    public function myProfile(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        if ($userId == $id) {

            $getUser = User::find($id);

            if (isset($getUser)) {
                return $getUser;
            } else {
                return response([
                    'message' => 'No user found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ], 401);
        }
    }

    public function update(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;


        //dd($request->all());
        
        if ($userId == $id) {
            $user = User::find($id);
            $user->update($request->all());
            $user->save();

            return $user;
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ], 401);
        }
    }


    public function delete(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        if ($userId == $id) {
            $getUser = User::destroy($id);

            if ($getUser == 1) {
                return response([
                    'message' => 'User Deleted Succesfully'
                ]);
            } elseif ($getUser == 0) {
                return response([
                    'message' => 'Already deleted'
                ]);
            } else {
                return response([
                    'message' => 'No user found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ], 401);
        }
    }


    public function searchByName($name)
    {
        return User::where('name', 'like', '%' . $name . '%')->get();
    }
}
