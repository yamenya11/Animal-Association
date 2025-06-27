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
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AdminAd_midiaController;
use App\Http\Controllers\DonateController;
use App\Http\Controllers\StafController;
use App\Http\Controllers\ProfileController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::post('/user/toggle-availability', [AuthController::class, 'toggleAvailability'])->middleware('auth');

// ==== المسارات العامة (بدون مصادقة) ====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/animals/available', [AnimalController::class, 'available']); // عرض الحيوانات للتبني (للعامة)
Route::get('/posts/get', [PostController::class, 'show_all_post']); // عرض البوستات (للعامة)

// ==== المسارات التي تتطلب مصادقة (لجميع المستخدمين المسجلين) ====
Route::middleware('auth:sanctum')->group(function () {
    // الملف الشخصي
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::get('/profile/update', [ProfileController::class, 'update']);

    // المحفظة
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);

    // الإعلانات
    Route::post('/ads', [AdController::class, 'store']);
    Route::get('/ads/show/user', [AdController::class, 'show_All_Ads']);

    // التبني
    Route::post('/adoptions/request', [AdoptionController::class, 'requestAdoption']);
    Route::get('/adoptions/my', [AdoptionController::class, 'myAdoptions']);

    // البوستات
    Route::post('/posts', [PostController::class, 'store']);

    // التعليقات والإعجابات
    Route::post('/posts/{post}/comment', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    Route::post('/comments/{commentId}/reply', [CommentController::class, 'reply']);
    Route::get('posts/{postId}/comments', [CommentController::class, 'index']);

    // التطوع
    Route::post('/volunteer/apply', [VolunteerController::class, 'apply']);


  Route::get('/appointments/client', [AppointmentController::class, 'showAppointmentMyUser']);//عرض مواعيد المستخدم


    // الإشعارات
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/animal-cases/immediate-request', [AnimalCaseController::class, 'store']);
    // مسارات أخرى عامة للمستخدمين المسجلين

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/donations', [DonateController::class, 'approvedDonations']);});


  //التبرع
   Route::post('/donates', [DonateController::class, 'create_donate'])
     ->middleware('auth:sanctum');





// ==== مسارات الموظفين (Employee) ====
 Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
    Route::get('/animal-cases', [AnimalCaseController::class, 'index']); // عرض حالات الحيوانات
    Route::post('/appointments/request', [AppointmentController::class, 'request']); // طلب موعد
    Route::post('/employee/appointment/{id}/respond', [AppointmentController::class, 'respond']);
    //Route::post('/animal-cases/immediate-request', [AnimalCaseController::class, 'immediateRequest']);


    Route::get('/volunteer/requests/employee', [VolunteerController::class, 'index']); // عرض طلبات التطوع
    Route::post('/volunteer/requests/{id}/respons', [VolunteerController::class, 'respond']); // الرد على طلب التطوع

    Route::get('/animal-cases/immediate', [StafController::class, 'listImmediateCases']);
    Route::get('/doctors', [StafController::class, 'availableDoctors']);
    Route::post('/appointments/schedule/{caseId}', [StafController::class, 'scheduleImmediate']);


    Route::get('/cases/regular', [StafController::class, 'listRegularCases']);//عرض الحالات العادية
    Route::get('/cases/immediate', [StafController::class, 'listImmediateCases']); //عرض الحالات الضرورية

    Route::post('/user/toggle-availability', [AuthController::class, 'toggleAvailability']);
});

// Route::prefix('employee')->group(function () {

// });



// ==== مسارات الإدمن (Admin) ====
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/volunteer/requests', [VolunteerController::class, 'index']);
    Route::post('/volunteer/requests/{id}', [VolunteerController::class, 'respond']);
    Route::post('/admin/posts/{id}/respond', [AdminPostController::class, 'respond']);
    Route::post('/admin/adopt/{id}/respond_adopt', [AdminPostController::class, 'respond_Adopt']);
    Route::get('/admin/posts', [AdminPostController::class, 'index']);
    Route::get('/admin/adoptions', [AdminPostController::class, 'getAllAdoptionRequests']);
    Route::get('/admin/ads/pending', [AdminAd_midiaController::class, 'index']);
    Route::post('/admin/ads/{adId}/respond', [AdminAd_midiaController::class, 'respond']);
    Route::post('/donates/{id}/respond', [DonateController::class, 'respond']);
    Route::get('/donates', [DonateController::class, 'index']);
});


















Route::middleware('auth:sanctum')->group(function () {
    Route::get('/events/user', [AdminController::class, 'listActiveEvents']);
    Route::post('/events/{eventId}/register', [AdminController::class, 'registerForEvent']);
});








///////////////////////////////////fromyaser
// مسارات المدير
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // إدارة المستخدمين
     Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);

    // إدارة الخدمات
    Route::get('/admin/services', [AdminController::class, 'getServices']);
    Route::post('/admin/services', [AdminController::class, 'addService']);
    Route::put('/admin/services/{serviceId}', [AdminController::class, 'updateService']);
    Route::delete('/admin/services/{serviceId}', [AdminController::class, 'deleteService']);

    // // التقارير
    Route::get('/admin/reports/performance', [AdminController::class, 'getPerformanceReport']);
    Route::get('/admin/reports/daily', [AdminController::class, 'getDailyReport']);

    // // مسارات إدارة الفعاليات للمدير
        Route::get('/events', [AdminController::class, 'getEvents']);
        Route::post('/events', [AdminController::class, 'createEvent']);
        Route::put('/events/{eventId}', [AdminController::class, 'updateEvent']);
        Route::delete('/events/{eventId}', [AdminController::class, 'deleteEvent']);
        Route::get('/events/{eventId}/participants', [AdminController::class, 'getEventParticipants']);
        Route::put('/events/{eventId}/participants/{participantId}', [AdminController::class, 'updateParticipantStatus']);
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
Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
     Route::get('/animals', [EmployeeController::class, 'index']);           // عرض كل الحيوانات
    Route::get('/animals/{id}', [EmployeeController::class, 'show']);       // عرض حيوان محدد
    Route::post('/animals', [EmployeeController::class, 'store']);          // إضافة حيوان
    Route::put('/animals/{id}', [EmployeeController::class, 'update']);     // تعديل حيوان
    Route::delete('/animals/{id}', [EmployeeController::class, 'destroy']); // حذف حيوان
    Route::post('/animals/upload-image', [EmployeeController::class, 'uploadImage']);
});