<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AnimalController;
use App\Http\Controllers\API\AdoptionController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\AnimalCaseController;
use App\Http\Controllers\API\AppointmentController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::get('/animals/available', [AnimalController::class, 'available']);  ///عرض الحيوانات للتبني

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/adoptions/request', [AdoptionController::class, 'requestAdoption']);
    Route::get('/adoptions/my', [AdoptionController::class, 'myAdoptions']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
   // Route::get('/posts', [PostController::class, 'index']);
});

Route::middleware('auth:sanctum')->post('/animal-cases', [AnimalCaseController::class, 'store']);

Route::middleware('auth:sanctum')->post('/appointments/request', [AppointmentController::class, 'request']);
Route::middleware('auth:sanctum')->get('/appointments/pending', [AppointmentController::class, 'pending']);