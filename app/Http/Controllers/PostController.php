<?php

/* This is not working requires debugging will be doing as the next thing */


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function findAll()
    {
       return Post::all();
    }


    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'title' => 'required',
            'body' => 'required',
        ]);

        return Post::create($request->all());
    }


    public function findById($id)
    {
        return Post::find($id);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        $post->update($request->all());

        return $post;
    }


    public function delete($id)
    {
        return Post::destroy($id);
    }


    public function searchByTitle($title)
    {
        return Post::where('title', 'like', '%' . $title . '%')->get();
    }

}
