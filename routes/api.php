<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Public Routes
// Route::post('/register', [AuthController::class, 'register'])->middleware('Cauth');


//Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/EmailConfirmation/{email}/{token}', [AuthController::class, 'EmailConfirmation']);

//Grouped Middleware
Route::middleware(['Cauth'])->group(function () {
    //Posts Routes
    Route::post('/posts', [PostController::class, 'create']);
    Route::get('/posts', [PostController::class, 'findAll']);
    Route::get('/posts/{id}', [PostController::class, 'findById']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'delete']);
    Route::post('/posts/{title}', [PostController::class, 'searchByTitle']);
    Route::post('/posts/changePrivacy/{id}', [PostController::class, 'changePrivacy']);

    //Users Routes
    Route::get('/users/myprofile/{id}', [UserController::class, 'myProfile']);
    Route::put('/users/update/{id}', [UserController::class, 'update']);
    Route::delete('/users/delete/{id}', [UserController::class, 'delete']);
    Route::post('/users/search/{name}', [UserController::class, 'searchByName']);

    //Friend Request
    Route::post('/sendRequest/{id}', [FriendRequestController::class, 'sendRequest']);
    Route::get('/myRequests', [FriendRequestController::class, 'myRequests']);
    Route::get('/acceptRequest/{id}', [FriendRequestController::class, 'acceptRequest']);
    Route::get('/deleteRequest/{id}', [FriendRequestController::class, 'deleteRequest']);
    Route::get('/removeFriend/{id}', [FriendRequestController::class, 'removeFriend']);
});





Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
