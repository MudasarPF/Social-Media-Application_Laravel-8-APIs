<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function PHPUnit\Framework\isEmpty;

class PostController extends Controller
{

    /*
        Returns user and its friends post
    */
    public function findAll(Request $request)
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



        //Get friends of this user
        $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id');
        $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id');


        //Get Posts of friends
        $friendsPost = Post::whereIn('user_id', $sentRequests)->orwhereIn('user_id', $recievedRequests)->orwhere('user_id', $userId)->orwhere('privacy', false)->get();


        return $friendsPost;
    }


    /*
        Function to find a post by id
        returns if the post is yours, your friends or a public post
        parameter: post_id
    */
    public function findById(Request $request, $id)
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

        //Get friends of this user
        $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
        $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

        $getPost = Post::find($id);

        //user_id of author of this post
        $author = $getPost->user_id;


        if (in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false) {

            if (isset($getPost)) {
                return $getPost;
            } else {
                return response([
                    'message' => 'No Post found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not allowed to access this post'
            ], 404);
        }
    }


    /*
        Function to create a post.
    */
    public function create(Request $request)
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


        $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);


        if ($request->file('attachment') != null) {
            $file = $request->file('attachment')->store('postFiles');

            return Post::create([
                'user_id' => $userId,
                'title' => $request->title,
                'body' => $request->body,
                'attachment' => 'http://127.0.0.1:8000/storage/app/' . $file,
            ]);
        } else {
            return Post::create([
                'user_id' => $userId,
                'title' => $request->title,
                'body' => $request->body,
            ]);
        }
    }



    /*
        Function to update a post
        parameter: post_id
    */
    public function update(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $request->validate([
            'privacy' => 'boolean',
        ]);


        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        $post = Post::where('user_id', $userId)->where('id', $id)->first();

        //dd($request->file('attachment'));

        if ($post) {
            $post->update($request->all());

            if ($request->file('attachment') != null) {
                $file = $request->file('attachment')->store('postFiles');
                $post->attachment = 'http://127.0.0.1:8000/storage/app/' . $file;
            }

            if ($request->privacy != null) {
                $post->privacy = $request->privacy;
            }

            return $post;
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ]);
        }
    }


    /*
        Function to delete a post
        parameter: post_id
    */
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

        $post = Post::where('user_id', $userId)->where('id', $id)->first();

        if (!$post) {
            return response([
                'message' => 'You are not authorized to perform this action'
            ]);
        }

        $post->delete();

        return response([
            'message' => 'Post Deleted Succesfully'
        ]);
    }

    /*
        Function to search a post by title
    */
    public function searchByTitle($title)
    {
        return Post::where('title', 'like', '%' . $title . '%')->get();
    }



    /*
    Function to change the privacy of a post
    -----------------------------------
    Post privacy is set false as default
    privacy->false means PUBLIC
    privacy->true means PRIVATE
    */
    public function changePrivacy(Request $request, $id)
    {
        //Get Bearer Token
        $getToken = $request->bearerToken();

        if (!isset($getToken)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $request->validate([
            'privacy' => 'required|boolean',
        ]);

        //Decode
        $decoded = JWT::decode($getToken, new Key('ProgrammersForce', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        $post = Post::where('user_id', $userId)->where('id', $id)->first();

        if (!$post) {
            return response([
                'message' => 'You are not authorized to perform this action'
            ]);
        }

        $post->privacy = $request->privacy;

        if ($request->privacy == true) {
            return response([
                'message' => 'Post privacy changed succesfully',
                'status' => 'Post is private now'
            ]);
        } else {
            return response([
                'message' => 'Post privacy changed succesfully',
                'status' => 'Post is public now'
            ]);
        }
    }
}
