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
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\NotificationController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route:: post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});


//المحفظة
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
});


//اضافة اعلان
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ads', [AdController::class, 'store']);
    Route::get('/ads/show/user', [AdController::class, 'show_All_Ads']);//عرض الاعلانات للمستخدمين
});

Route::get('/animals/available', [AnimalController::class, 'available']);  /// للعامة عرض الحيوانات للتبني


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/adoptions/request', [AdoptionController::class, 'requestAdoption']);//طلب تبني
    Route::get('/adoptions/my', [AdoptionController::class, 'myAdoptions']);  //عرض طلبت التبني للمستخدم
});

//////////////

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
   
});

Route::get('/posts/get', [PostController::class, 'show_all_post']);//عرض البوستات   للعامة

//////////////////

Route::middleware('auth:sanctum')->post('/animal-cases', [AnimalCaseController::class, 'store']);

Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
Route::get('/animal-cases', [AnimalCaseController::class, 'index']);  //عرض حالات الحيوانات المصابة
Route::post('/appointments/request', [AppointmentController::class, 'request']);
Route::get('/volunteer/requests', [VolunteerController::class, 'index']);
Route::post('/volunteer/requests/{id}', [VolunteerController::class, 'respond']);

});


Route::middleware('auth:sanctum')->get('/appointments/pending', [AppointmentController::class, 'pending']); //vet

Route::middleware('auth:sanctum')->group(function(){
    // المستخدم يرسل طلب تطوع
    Route::post('/volunteer/apply', [VolunteerController::class, 'apply']);
 
});



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/{post}/comment', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    Route::post('/comments/{commentId}/reply', [CommentController::class, 'reply']);
    Route::get('posts/{postId}/comments', [CommentController::class, 'index']);

});

   // المسارات الخاصة بالإدمن فقط
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/volunteer/requests', [VolunteerController::class, 'index']);
    Route::post('/volunteer/requests/{id}', [VolunteerController::class, 'respond']);
    Route::post('/admin/posts/{id}/respond', [AdminPostController::class, 'respond']);
     Route::post('/admin/adopt/{id}/respond_adopt', [AdminPostController::class, 'respond_Adopt']);
    Route::get('/admin/posts', [AdminPostController::class, 'index']);
    Route::get('/admin/adoptions', [AdminPostController::class, 'getAllAdoptionRequests']);//عرض طلبات التبني
    Route::get('/admin/ads/pending', [AdminAd_midiaController::class, 'index']);
    // الرد على إعلان (موافقة أو رفض)
    Route::post('/admin/ads/{adId}/respond', [AdminAd_midiaController::class, 'respond']);
});

Route::middleware('auth:sanctum')->group(function () {
    // جلب كل الإشعارات للمستخدم الحالي
    Route::get('/notifications', [NotificationController::class, 'index']);
    // تعيين إشعار كمقروء
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

// مسارات المدير
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // إدارة المستخدمين
    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::put('/admin/users/{userId}', [AdminController::class, 'updateUser']);
    Route::delete('/admin/users/{userId}', [AdminController::class, 'deleteUser']);

    // إدارة الخدمات
    Route::get('/admin/services', [AdminController::class, 'getServices']);
    Route::post('/admin/services', [AdminController::class, 'addService']);
    Route::put('/admin/services/{serviceId}', [AdminController::class, 'updateService']);
    Route::delete('/admin/services/{serviceId}', [AdminController::class, 'deleteService']);

    // التقارير
    Route::get('/admin/reports/performance', [AdminController::class, 'getPerformanceReport']);
    Route::get('/admin/reports/daily', [AdminController::class, 'getDailyReport']);

    // مسارات إدارة الفعاليات للمدير
    Route::get('/admin/events', [AdminController::class, 'getEvents']);
    Route::post('/admin/events', [AdminController::class, 'createEvent']);
    Route::put('/admin/events/{eventId}', [AdminController::class, 'updateEvent']);
    Route::delete('/admin/events/{eventId}', [AdminController::class, 'deleteEvent']);
    Route::get('/admin/events/{eventId}/participants', [AdminController::class, 'getEventParticipants']);
    Route::put('/admin/events/{eventId}/participants/{participantId}', [AdminController::class, 'updateParticipantStatus']);
});

// مسارات الموظف
Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
    // إدارة المستخدمين
    Route::get('/employee/users', [EmployeeController::class, 'getUsers']);

    // إدارة المحتوى
    Route::get('/employee/content/pending', [EmployeeController::class, 'getPendingContent']);
    Route::post('/employee/content/{contentId}/approve', [EmployeeController::class, 'approveContent']);
    Route::post('/employee/content/{contentId}/reject', [EmployeeController::class, 'rejectContent']);

    // التقارير
    Route::get('/employee/reports/daily', [EmployeeController::class, 'getDailyReport']);

    // التواصل مع المتطوعين
    Route::get('/employee/volunteers', [EmployeeController::class, 'getVolunteers']);
    Route::post('/employee/volunteers/{volunteerId}/message', [EmployeeController::class, 'sendMessageToVolunteer']);

    // مسارات الفعاليات للموظفين
    Route::get('/employee/events', [EmployeeController::class, 'getAvailableEvents']);
    Route::post('/employee/events/{eventId}/register', [EmployeeController::class, 'registerForEvent']);
    Route::post('/employee/events/{eventId}/cancel', [EmployeeController::class, 'cancelEventRegistration']);
    Route::get('/employee/my-events', [EmployeeController::class, 'getMyEvents']);
});

// مسارات إدارة الحيوانات
Route::middleware('auth:sanctum', 'role:admin')->group(function () {
    Route::get('/animals', [AnimalController::class, 'index']);
    Route::get('/animals/{id}', [AnimalController::class, 'show']);
    Route::post('/animals', [AnimalController::class, 'store']);
    Route::put('/animals/{id}', [AnimalController::class, 'update']);
    Route::delete('/animals/{id}', [AnimalController::class, 'destroy']);
    Route::post('/animals/upload-image', [AnimalController::class, 'uploadImage']);
});