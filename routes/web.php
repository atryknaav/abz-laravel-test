<?php

use App\Http\Controllers\PositionController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/users');

Route::get('/token', [TokenController::class, 'create']);

Route::get('/positions', [PositionController::class, 'index']);

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store'])->middleware('token');
