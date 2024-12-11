<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieFunctionController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::prefix('user')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [RegisterController::class, 'index']);  // Obtener todos los usuarios
    Route::get('/{user}', [RegisterController::class, 'show']);
    Route::delete('/{user}', [RegisterController::class, 'destroy']);
});

// Ruta protegida para cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Rutas protegidas por autenticación y rol de admin
Route::prefix('genres')->middleware(['auth:sanctum', 'role:admin'])->group(function () {    
    Route::get('/', [GenreController::class, 'index']);
    Route::get('/{genre}', [GenreController::class, 'show']);
    Route::post('/', [GenreController::class, 'store']);
    Route::put('/{genre}', [GenreController::class, 'update']);
    Route::delete('/{genre}', [GenreController::class, 'destroy']);
});

// Rutas públicas de películas
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'index']);
});

// Ruta para poder ver las peliculas por id
Route::prefix('movies')->middleware(['auth:sanctum', 'role:admin|user'])->group(function () {
       Route::get('/{movie}', [MovieController::class, 'show']);
});

// Rutas protegidas por autenticación y rol de admin
Route::prefix('movies')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/', [MovieController::class, 'store']);
    Route::put('/{movie}', [MovieController::class, 'update']);
    Route::delete('/{movie}', [MovieController::class, 'destroy']);
});


// Rutas para Rooms protegidas por autenticación y rol de admin
Route::prefix('rooms')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/', [RoomController::class, 'store']);
    Route::get('/', [RoomController::class, 'index']);
    Route::get('/{room}', [RoomController::class, 'show']);
    Route::put('/{room}', [RoomController::class, 'update']);
    Route::delete('/{room}', [RoomController::class, 'destroy']);
});

// Rutas para Movie Functions protegidas por autenticación y rol de admin
Route::prefix('movie-functions')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/', [MovieFunctionController::class, 'store']);
    Route::get('/', [MovieFunctionController::class, 'index']);
    Route::get('/{movie_function}', [MovieFunctionController::class, 'show']);
    Route::put('/{movie_function}', [MovieFunctionController::class, 'update']);
    Route::delete('/{movie_function}', [MovieFunctionController::class, 'destroy']);
});


Route::prefix('tickets')->middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::post('/', [TicketController::class, 'store']);
});

// Ruta para obtener ticket por código
Route::prefix('tickets')->middleware(['auth:sanctum', 'role:empleado'])->group(function () {
    Route::get('/codigo/{ticketCode}', [TicketController::class, 'showByCode']);
});


// Rutas para Tickets protegidas por autenticación y rol de admin
Route::prefix('tickets')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [TicketController::class, 'index']);
    Route::delete('/{ticket}', [TicketController::class, 'destroy']);
});


// Rutas protegidas para AccountController, solo accesibles por administradores
Route::prefix('accounts')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [AccountController::class, 'index']);
    Route::post('/', [AccountController::class, 'store']);
    Route::get('/{accounts}', [AccountController::class, 'show']);
    Route::put('/{accounts}', [AccountController::class, 'update']);
    Route::delete('/{accounts}', [AccountController::class, 'destroy']);
});

