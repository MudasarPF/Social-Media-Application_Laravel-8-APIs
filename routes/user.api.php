<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::middleware(['Cauth'])->group(function () {
    Route::get('/users/myprofile/{id}', [UserController::class, 'myProfile']);
    Route::put('/users/update/{id}', [UserController::class, 'update']);
    Route::delete('/users/delete/{id}', [UserController::class, 'delete']);
    Route::post('/users/search/{name}', [UserController::class, 'searchByName']);
});
