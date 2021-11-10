<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;

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

    //Friend Request
    Route::post('/sendRequest', [FriendRequestController::class, 'sendRequest']);
});





Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
