<?php

use App\Http\Controllers\PositionController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/users');

Route::get('/token', [TokenController::class, 'create']);


Route::get('/positions/{any}', function ($any) {
    //instead of error handling (as there are no errors to throw potentially)
    return redirect("/positions");
})->where('any', '.*');
Route::get('/positions', [PositionController::class, 'index']);

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store'])->middleware('token');
