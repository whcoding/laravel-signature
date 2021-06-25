<?php


use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/login', [UserController::class, 'login']);
    Route::middleware('auth.signature')->get('/user', [UserController::class, 'test']);
});

