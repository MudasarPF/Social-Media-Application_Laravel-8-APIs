<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FriendRequestController extends Controller
{
    public function sendRequest(Request $request)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $fields = $request->validate([
            'receiver_id' => 'required|integer',
        ]);

        $userExists = User::where('id', $fields['receiver_id'])->first();

        if(!isset($userExists))
        {
            return response([
                'message' => 'Request receiver does not exist'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        /*Start working from here */
        $requestExists = FriendRequest::where('receiver_id', $fields['receiver_id'] || 'sender_id', $fields['receiver_id'])->first();

        dd($requestExists);


         //Store friend request in database
         $saveFriendRequest = FriendRequest::create([
            'sender_id' => $userId,
            'receiver_id' => $fields['receiver_id'],
            'status' => false
        ]);

        return response([
            'message' => 'Request sent to ' . $userExists->name
        ]);
    }
}
