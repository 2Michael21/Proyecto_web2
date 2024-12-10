<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieFunctionController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('guest:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::prefix('genres')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [GenreController::class, 'index']);
    Route::post('/', [GenreController::class, 'store']);
    Route::get('/{genre}', [GenreController::class, 'show']);
    Route::put('/{genre}', [GenreController::class, 'update']);
    Route::delete('/{genre}', [GenreController::class, 'destroy']);
});


Route::prefix('movies')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MovieController::class, 'index']);
    Route::post('/', [MovieController::class, 'store']);
    Route::get('/{movie}', [MovieController::class, 'show']);
    Route::put('/{movie}', [MovieController::class, 'update']);
    Route::delete('/{movie}', [MovieController::class, 'destroy']);
});


Route::apiResource('rooms', RoomController::class)->middleware('auth:sanctum');

Route::apiResource('movie-functions', MovieFunctionController::class)->middleware('auth:sanctum');

Route::apiResource('tickets', TicketController::class)->middleware('auth:sanctum');
