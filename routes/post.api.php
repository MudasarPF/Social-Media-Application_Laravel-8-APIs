<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;


Route::middleware(['Cauth'])->group(function () {
    Route::post('/posts', [PostController::class, 'create']);
    Route::get('/posts', [PostController::class, 'findAll']);
    Route::get('/posts/{id}', [PostController::class, 'findById']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'delete']);
    Route::post('/posts/{title}', [PostController::class, 'searchByTitle']);
    Route::post('/posts/changePrivacy/{id}', [PostController::class, 'changePrivacy']);
});

?>