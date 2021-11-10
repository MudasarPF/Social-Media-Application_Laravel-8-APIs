<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PostController extends Controller
{
    public function findAll()
    {
        return Post::all();
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

        if($request->file('attachment') != null)
        {
            $file = $request->file('attachment')->store('postFiles');

            return Post::create([
                'user_id' => $userId,
                'title' => $request->title,
                'body' => $request->body,
                'attachement' => 'http://127.0.0.1:8000/storage/app/' . $file
            ]);
        }
        else
        {
            return Post::create([
                'user_id' => $userId,
                'title' => $request->title,
                'body' => $request->body,
            ]);
        }
    }


    public function findById($id)
    {
        $getPost = Post::find($id);

        if (isset($getPost)) {
            return $getPost;
        } else {
            return response([
                'message' => 'No Post found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        $post->update($request->all());

        return $post;
    }


    public function delete($id)
    {
        $getPost = Post::destroy($id);

        if ($getPost == 1) {
            return response([
                'message' => 'Post Deleted Succesfully'
            ]);
        } elseif ($getPost == 0) {
            return response([
                'message' => 'Already deleted'
            ]);
        } else {
            return response([
                'message' => 'No Post found'
            ], 404);
        }
    }


    public function searchByTitle($title)
    {
        return Post::where('title', 'like', '%' . $title . '%')->get();
    }
}
