<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestReceivedResource;
use App\Http\Resources\RequestSentResource;
use App\Models\FriendRequest;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class FriendRequestController extends Controller
{

    /*
        Function to send a request to another user
        parameter: user_id
    */
    public function sendRequest(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);

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

            //User can not send request to itself
            if ($userId == $id) {
                return response([
                    'message' => 'You can not send request to yourself'
                ]);
            }

            /* 
            Check if request has been to this user before
                                OR
            Request has been received from this user before
        */
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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to show requests of the user
    */
    public function myRequests(Request $request)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            if (!isset($getToken)) {
                return response([
                    'message' => 'Bearer token not found'
                ]);
            }

            $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId);
            $requestsSent =  SentFriendRequest::all()->where('user_id', $userId);

            if ((json_decode($requestsReceived)) == null && (json_decode($requestsSent)) == null) {
                return response([
                    'message' => 'You have no friend requests'
                ]);
            } else {
                return response([
                    'requests_sent' => RequestSentResource::collection($requestsSent),
                    'requests_received' => RequestReceivedResource::collection($requestsReceived)
                ]);
            }
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to accept a request received by the user.
        Takes id of the user who sent the request as a parameter
        parameter: user_id
    */
    public function acceptRequest(Request $request, $id)
    {
        try {

            //Get Id
            $userId = getUserId($request);

            if (!isset($getToken)) {
                return response([
                    'message' => 'Bearer token not found'
                ]);
            }

            //Check if request has been received
            $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->first();

            //Get corresponding entry from sent request table too to change status in both tables
            $requestsSent =  SentFriendRequest::all()->where('user_id', $id)->where('receiver_id', $userId)->first();


            if (isset($requestsReceived)) {

                if ($requestsReceived->status ==  true && $requestsSent->status == true) {
                    return response([
                        'message' => 'Request already accepted'
                    ]);
                }

                $requestsReceived->status = true;
                $requestsReceived->save();

                //Change status for sender too
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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to delete a request either sent or recieved by you.
        parameter: user_id
    */
    public function deleteRequest(Request $request, $id)
    {
        try {

            //Get Id
            $userId = getUserId($request);

            if (!isset($getToken)) {
                return response([
                    'message' => 'Bearer token not found'
                ]);
            }

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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Function to remove a friend from the list.
        parameter: user_id
    */
    public function removeFriend(Request $request, $id)
    {
        try {

            //Get Id
            $userId = getUserId($request);

            if (!isset($getToken)) {
                return response([
                    'message' => 'Bearer token not found'
                ]);
            }

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
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }
}
