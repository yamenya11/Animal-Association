<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AnimalController;
use App\Http\Controllers\API\AdoptionController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\AnimalCaseController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\VolunteerController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\Post\CommentController;
use App\Http\Controllers\Post\LikeController;
use Spatie\Permission\Models\Role;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});
Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);


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
Route::middleware('auth:sanctum')->get('/appointments/pending', [AppointmentController::class, 'pending']); //vet



Route::middleware('auth:sanctum')->group(function(){
    // المستخدم يرسل طلب تطوع
    Route::post('/volunteer/apply', [VolunteerController::class, 'apply']);

 
});

Route::middleware('auth:sanctum')->group(function () {
     Route::post('/posts/{post}/comment', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
});

   // المسارات الخاصة بالإدمن فقط
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/volunteer/requests', [VolunteerController::class, 'index']);
    Route::post('/volunteer/requests/{id}', [VolunteerController::class, 'respond']);
    Route::post('/admin/posts/{id}/respond', [AdminPostController::class, 'respond']);
    Route::get('/admin/posts', [AdminPostController::class, 'index']);
});