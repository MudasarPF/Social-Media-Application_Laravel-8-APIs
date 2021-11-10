<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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


//Grouped Middleware
Route::middleware(['Cauth'])->group(function()
{
    // Route::post('/register', [AuthController::class, 'register']);
});

//Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/EmailConfirmation/{email}/{token}', [AuthController::class, 'EmailConfirmation']);


//Posts Routes
Route::post('/posts', [PostController::class, 'create']);
Route::get('/posts', [PostController::class, 'findAll']);
Route::get('/posts/{id}', [PostController::class, 'findById']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::post('/posts/{id}', [PostController::class, 'delete']);
Route::post('/posts/{title}', [PostController::class, 'searchByTitle']);



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
