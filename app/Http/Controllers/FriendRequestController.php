<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Middleware;

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

        if (!isset($userExists)) {
            return response([
                'message' => 'Request receiver does not exist'
            ]);
        }

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        //User can not send request to itself
        if ($userId == $fields['receiver_id']) {
            return response([
                'message' => 'You can not send request to yourself'
            ]);
        }

        /*Start working from here */
        $receiverExists = FriendRequest::where('receiver_id', $fields['receiver_id'])->first();
        $senderExists = FriendRequest::where('sender_id', $fields['receiver_id'])->first();

        if ($receiverExists == null && $senderExists == null) {
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
        else
        {
            return response([
                'message' => 'Friend request is already pending'
            ]);
        }
    }


    public function myRequests($id)
    {
        $requests =  FriendRequest::all()->where('receiver_id' , $id);

        if((json_decode($requests)) == null)
        {
            return response([
                'message' => 'You have no friend requests'
            ]);
        }
        else
        {
            return $requests;
        }
    }


    public function acceptRequest(Request $request, $id)
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

        //Person who sent the request
        $senderEmail =  FriendRequest::all()->where('sender_id' , $id)->first();
        
        //Person who received the request
        $receiverEmail =  FriendRequest::all()->where('receiver_id' , $userId)->first();



        if(isset($senderEmail))
        {
            if($receiverEmail)
            {
                if($receiverEmail-> status ==  true)
                {
                    return response([
                        'message' => 'Request already accepted'
                    ]);
                }
                
                $receiverEmail->status = true;
                $receiverEmail->save();

                return response([
                    'message' => 'Request accepted'
                ]);
            }
            else
            {
                return response([
                    'message' => 'You are not authorized to perform this action'
                ]);
            }
        }
        else
        {
            return response([
                'message' => 'You do not have this particular request'
            ]);
        }
    }
}



/*
Middleware -> DONE
NameConvention
Database naming convention
Migration relation
Validation use form request
migrate:fresh -> DONE
*/