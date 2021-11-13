<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FriendRequestController extends Controller
{
    public function sendRequest(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $userExists = User::where('id', $id)->first();

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
        if ($userId == $id) {
            return response([
                'message' => 'You can not send request to yourself'
            ]);
        }

        /* Old logic where i was using single table to store friend requests */
        // $combinedQuery1 = FriendRequest::all()->where('sender_id', $userId)->where('receiver_id', $fields['receiver_id'])->first();
        // $combinedQuery2 = FriendRequest::all()->where('sender_id', $fields['receiver_id'])->where('receiver_id', $userId)->first();



        /* New logic using two different tables for sent and received requests */
        $requestsSent = SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->first();
        $requestsReceived = ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->first();

        if ($requestsSent == null && $requestsReceived == null) {
            //Enter data in both tables

            $saveFriendRequest1 = SentFriendRequest::create([
                'user_id' => $userId,
                'receiver_id' => $id,
                'status' => false
            ]);


            $saveFriendRequest2 = ReceivedFriendRequest::create([
                'sender_id' => $userId,
                'user_id' => $id,
                'status' => false
            ]);

            return response([
                'message' => 'Request sent to ' . $userExists->name
            ]);
        } else {
            return response([
                'message' => 'Friend request is already pending'
            ]);
        }
    }


    public function myRequests(Request $request)
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

        $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId);
        $requestsSent =  SentFriendRequest::all()->where('user_id', $userId);

        if ((json_decode($requestsReceived)) == null && (json_decode($requestsSent)) == null) {
            return response([
                'message' => 'You have no friend requests'
            ]);
        } else {
            return response([
                'requests_sent' => $requestsSent,
                'requests_received' => $requestsReceived
            ]);
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


        /*
        This commented code is for old the logic.
        It will be removed at the appropriate time
        I am keeping this code in case i need it later
        
        //Person who received the request
        //$receiverEmail =  FriendRequest::all()->where('receiver_id', $userId)->first();

        //Old Logic of one single table of requests
        //$combinedQuery = FriendRequest::all()->where('sender_id', $id)->where('receiver_id', $userId)->first();
        */

        $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->first();


        if (isset($requestsReceived)) {

            if ($requestsReceived->status ==  true) {
                return response([
                    'message' => 'Request already accepted'
                ]);
            }

            $requestsReceived->status = true;
            $requestsReceived->save();

            //Change status for sender too
            $requestsSent =  SentFriendRequest::all()->where('user_id', $id)->first();
            
            if (isset($requestsSent)) {
                $requestsSent->status = true;
                $requestsSent->save();
            }


            return response([
                'message' => 'Request accepted'
            ]);
        } else {
            return response([
                'message' => 'You are not allowed to perform this action'
            ]);
        }


        /*
        I am keeping this code in case i need it later
        */


        // if (isset($requestsReceived)) {
        //     if ($receiverEmail) {
        //         if ($receiverEmail->status ==  true) {
        //             return response([
        //                 'message' => 'Request already accepted'
        //             ]);
        //         }

        //         $receiverEmail->status = true;
        //         $receiverEmail->save();

        //         return response([
        //             'message' => 'Request accepted'
        //         ]);
        //     } else {
        //         return response([
        //             'message' => 'You are not authorized to perform this action'
        //         ]);
        //     }
        // } else {
        //     return response([
        //         'message' => 'You do not have this particular request'
        //     ]);
        // }
    }


    public function deleteRequest(Request $request, $id)
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

        /*
        //Get the row where sender is the passed Id and receiver is the loggedin user
        $combinedQuery = FriendRequest::all()->where('sender_id', $id)->where('receiver_id', $userId)->where('status', false)->first();
        */

        $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->where('status', false)->first();

        $requestsSent =  SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->where('status', false)->first();


        if (isset($requestsReceived)) {
            $requestsReceived->delete();

            //Delete its corresponding entry from sent friend request table
            $sentRequest =  SentFriendRequest::all()->where('user_id', $id)->first();
            $sentRequest->delete();

            return response([
                'message' => 'Request deleted'
            ]);
        }

        if (isset($requestsSent)) {
            $requestsSent->delete();


            //Delete its corresponding entry from received friend request table
            $receivedRequest =  ReceivedFriendRequest::all()->where('user_id', $id)->first();
            $receivedRequest->delete();

            return response([
                'message' => 'You have unsent the request'
            ]);
        }

        return response([
            'message' => 'No such request exists'
        ]);
    }


    public function removeFriend(Request $request, $id)
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


        /*
        Kept for later use
        */
        //Get the row where sender is the passed Id and receiver is the loggedin user
        //$combinedQuery = FriendRequest::all()->where('sender_id', $id)->where('receiver_id', $userId)->where('status', true)->first();


        $requestsSent = SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->where('status', true)->first();
        $requestsReceived = ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->where('status', true)->first();


        if (isset($requestsReceived)) {
            $requestsReceived->delete();

            //Delete its corresponding entry from sent friend request table
            $sentRequest =  SentFriendRequest::all()->where('user_id', $id)->first();
            $sentRequest->delete();

            return response([
                'message' => 'You have removed a friend from your list'
            ]);
        }


        if (isset($requestsSent)) {
            $requestsSent->delete();

            //Delete its corresponding entry from received friend request table
            $receivedRequest =  ReceivedFriendRequest::all()->where('user_id', $id)->first();
            $receivedRequest->delete();

            return response([
                'message' => 'You have removed a friend from your list'
            ]);
        }


        return response([
            'message' => 'No such friend exists'
        ]);
    }
}



/*
Middleware -> DONE
NameConvention -> DONE
Database naming convention -> DONE
Migration relation ->  X
Validation use form request -> X
migrate:fresh -> DONE
*/