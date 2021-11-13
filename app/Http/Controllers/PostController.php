<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PostController extends Controller
{
    public function findAll(Request $request)
    {
        return Post::all()->where('privacy', false);
    }

    
    public function findById($id)
    {
        $getPost = Post::find($id);

        if (isset($getPost)) {
            if ($getPost->privacy == false) {
                return $getPost;
            }
            else
            {
                return response([
                    'message' => 'No such public post found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'No Post found'
            ], 404);
        }
    }


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


    public function searchByTitle($title)
    {
        return Post::where('title', 'like', '%' . $title . '%')->get();
    }

    /*
    Post privacy is set false as default

    privacy->false means PUBLIC
    privacy->true means PRIVATE (Only you and your friends)
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
