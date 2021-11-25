<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class UserController extends Controller
{
    /*
        Returns user profile
        parameter: user_id
    */
    public function myProfile(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            if ($userId == $id) {

                $getUser = User::find($id);

                if (isset($getUser)) {
                    return response()->success(new AuthResource($getUser));
                } else {
                    return response()->error('No user found', 404);
                }
            } else {
                return response()->error('You are not authorized to perform this action', 401);
            }
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }



    /*
        update user's data
    */
    public function update(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);


            //dd($request->all());

            if ($userId == $id) {
                $user = User::find($id);
                $user->update($request->all());
                $user->save();

                return response()->success($user);
            } else {
                return response()->error('You are not authorized to perform this action', 401);
            }
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }


    /*
        delete user's data
    */
    public function delete(Request $request, $id)
    {
        try {
            //Get Id
            $userId = getUserId($request);

            if ($userId == $id) {
                $getUser = User::destroy($id);

                if ($getUser == 1) {
                    return response()->success('User Deleted Succesfully');
                } elseif ($getUser == 0) {
                    return response()->error('Already deleted', 404);
                } else {
                    return response()->error('No user found', 404);
                }
            } else {
                return response()->error('You are not authorized to perform this action', 401);
            }
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }


    /*
        search user's by name
    */
    public function searchByName($name)
    {
        try {
            return response()->success(AuthResource::collection(User::where('name', 'like', '%' . $name . '%')->get()));
        } catch (Throwable $e) {
            return response()->error($e->getMessage());
        }
    }
}
