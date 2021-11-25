<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use App\Notifications\CommentOnYourPost;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Illuminate\Http\Request;
use Throwable;

class CommentController extends Controller
{

    /*
        Function to create a comment
        parameter: post_id
    */
    public function create(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            //Get friends of this user
            $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
            $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

            $getPost = Post::find($id);

            //user_id of author of this post
            $author = $getPost->user_id;

            //Get user
            $user = User::where('id', $author)->first();

            /*
            If author of the posts is
            > User's friend
            > User of the author of the post
            > Post is public
            allow user to update comment otherwise return unauthorized response
        */
            if (in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false) {

                $commentCreated =  Comment::create([
                    'user_id' => $userId,
                    'post_id' => $id,
                    'content' => $request->content,
                ]);

                $user->notify(new CommentOnYourPost($commentCreated));

                return response()->success($commentCreated, 201);
            } else {
                return response()->error('You are not allowed to comment on this post', 401);
            }
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }


    /*
        Updates comment by id
    */
    public function update(Request $request, $id)
    {
        try {

            //Get Id
            $userId = getUserId($request);

            //Get comment
            $getComment = Comment::find($id);

            if (!$getComment) {
                return response()->error('Comment does not exist', 404);
            }

            if (!isset($getToken)) {
                return response()->error('Bearer token not found', 404);
            }

            if ($request->content == null) {
                return response()->error('Content is required');
            }

            //Get friends of this user
            $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
            $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

            //Get Post id
            $postId = $getComment->post_id;
            $getPost = Post::find($postId);

            //user_id of author of this post
            $author = $getPost->user_id;

            //user_id of commenter
            $commenter = $getComment->user_id;

            /*
            If author of the posts is
            > User's friend
            > User is the author of the post
            > Post is public
            allow user to delete comment otherwise return unauthorized response
        */


            if ((in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false) && $commenter == $userId) {

                $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

                if ($comment) {
                    $comment->content = $request->content;
                    $comment->update();

                    return response()->success($comment, 204);
                } else {
                    return response()->error('Something went wrong', 404);
                }
            } else {
                return response()->error('You are not allowed to update this comment', 401);
            }
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }


    /*
        Deletes comment by id
    */

    public function delete(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            //Get comment
            $getComment = Comment::find($id);

            if (!$getComment) {
                return response()->error('Comment does not exist', 404);
            }

            if (!isset($getToken)) {
                return response()->error('Bearer token not found', 404);
            }

            //Get friends of this user
            $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
            $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

            //Get Post id
            $postId = $getComment->post_id;
            $getPost = Post::find($postId);

            //user_id of author of this post
            $author = $getPost->user_id;

            //user_id of commenter
            $commenter = $getComment->user_id;

            /*
            If author of the posts is
            > User's friend
            > User of the author of the post
            > Post is public
            allow user to comment otherwise return unauthorized response
        */
            if (in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false && $commenter == $userId) {

                $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

                if ($comment) {
                    $comment->delete();

                    return response()->success($comment, 204);
                } else {
                    return response()->error('You are not allowed to comment on this post', 401);
                }
            } else {
                return response()->error('You are not allowed to comment on this post', 401);
            }
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }


    /*
        Returns user's comments
    */
    public function myComments(Request $request)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            if (!isset($getToken)) {
                return response([
                    'message' => 'Bearer token not found'
                ]);
            }

            $comments = Comment::where('user_id', $userId)->get();

            return response()->success(CommentResource::collection($comments), 204);
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }
}
