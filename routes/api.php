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
use App\Http\Controllers\TemporaryCareController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\PolicyPostController;
use App\Http\Controllers\PolicyCommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VaccineController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\VolunteerTypeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AmbulanceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;
use \App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatisticsController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/guides', [GuideController::class, 'createGuide']);
            Route::post('/guides/{id}', [GuideController::class, 'updateGuide']);
            Route::delete('/guides/{id}', [GuideController::class, 'deleteGuide']);
             Route::get('/guides/fordoctor', [GuideController::class, 'getguidForDoctror']);
            Route::get('/guides', [GuideController::class, 'listAllByCategory']);
             Route::get('/guides/catgory', [GuideController::class, 'index']);
            Route::get('/guides/{id}', [GuideController::class, 'showGuide']);
          

        });

  Route::middleware('auth:sanctum')->post('/fcm/update-token', [FcmController::class, 'updateToken']);

        Route::middleware('auth:sanctum')->group(function () {

            // عرض المحادثات
            Route::get('conversations', [ChatController::class, 'indexConversations']);
            // إنشاء غروب (الموظف فقط)
            Route::post('conversations', [ChatController::class, 'createConversation']);
            // عرض الرسائل في محادثة
            Route::get('conversations/{conversation}/messages', [ChatController::class, 'messages']);
            // إرسال رسالة
            Route::post('conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
            // حذف رسالة من نسخة المستخدم فقط
            Route::delete('messages/{message}', [ChatController::class, 'deleteMessage']);
            //اضافة متطوعين
            Route::post('conversations/{conversation}/participants', [ChatController::class, 'addParticipant']);
        //عرض المتطوعين لاضافتهم للغروب
            Route::get('volunteers/type', [ChatController::class, 'getAllUsers']);
        //ازالة مشارك
        Route::delete('/conversations/{conversation}/participants/{user}', [ChatController::class, 'removeParticipant']);
            // حذف الغروب
        Route::delete('/conversations/{id}', [ChatController::class, 'deleteConversation']);
        // تغيير دور عضو 
        Route::post('/conversations/{conversation}/participants/{userId}/role', 
            [ChatController::class, 'changeUserRole']
        );
        // تعليم الرسالة كمقروءة
        Route::patch('/conversations/{conversation}/messages/{message}/read', 
            [ChatController::class, 'markAsRead']
        );
        //عرض اعضاء الغروب
        Route::get('/conversations/{conversation}/participants', [ChatController::class, 'getParticipants']);
        Route::get('/conversations/{conversation}/available-users', [ChatController::class, 'getAvailableUsers']);

        });

//Route::post('/user/toggle-availability', [AuthController::class, 'toggleAvailability'])->middleware('auth');

// ==== المسارات العامة (بدون مصادقة) ====
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/animals/available/user', [AnimalController::class, 'available']); // عرض الحيوانات للتبني (للعامة)
        Route::get('/posts/get', [PostController::class, 'show_all_post']); // عرض البوستات (للعامة)

// ==== المسارات التي تتطلب مصادقة (لجميع المستخدمين المسجلين) ====
    Route::middleware('auth:sanctum')->group(function () {
        // الملف الشخصي
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'profile']);
        Route::post('/profile/update', [ProfileController::class, 'update']);
        Route::post('/profile/image', [ProfileController::class, 'uploadImage']);
    Route::delete('/profile/image', [ProfileController::class, 'deleteProfileImage']);
        // المحفظة
        Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
        Route::get('/wallet/balance', [WalletController::class, 'balance']);

        // الإعلانات
        Route::post('/ads/user', [AdController::class, 'store']);
        Route::get('/ads/show/user', [AdController::class, 'show_All_Ads']);
        Route::get('/ads/{id}/publisher', [AdController::class, 'show']);
        Route::get('/user-ads', [AdController::class, 'showUserAds']);
        // التبني
        Route::post('/adoptions/store/re', [AdoptionController::class, 'requestAdoption']);
        Route::get('/adoptions/my', [AdoptionController::class, 'myAdoptions']);


        Route::post('/createRequest', [TemporaryCareController::class, 'createRequest']);

        // Route::post('/temporary-care/request', [TemporaryCareController::class, 'createRequest']);
        
        // الحصول على طلبات الرعاية للمستخدم الحالي
        Route::get('/temporary-care/my-requests', [TemporaryCareController::class, 'getUserRequests']);
        Route::get('/temporary-care/processed/user', [TemporaryCareController::class, 'processedRequests']);
        // الحصول على الحيوانات المتاحة للرعاية
        Route::get('/temporary-care/available-animals', [TemporaryCareController::class, 'getAvailableAnimals']);
        // البوستات
        Route::post('/posts', [PostController::class, 'store']);

        // التعليقات والإعجابات
        Route::post('/posts/{post}/comment', [CommentController::class, 'store']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
        Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
        Route::post('/comments/{commentId}/reply', [CommentController::class, 'reply']);
        Route::get('posts/{postId}/comments', [CommentController::class, 'index']);

        Route::get('/posts/{post}/likes-count', [LikeController::class, 'likesCount']);
        Route::get('/posts/likes-count/post', [LikeController::class, 'getAllLike']);    // التطوع
        Route::post('/volunteer/apply', [VolunteerController::class, 'apply']);
        Route::get('/volunteer-types/user', [VolunteerTypeController::class, 'index']);
        Route::get('/appointments/client', [AppointmentController::class, 'getProcessedAppointments']);//عرض مواعيد المستخدم
 
//COURSE
        Route::get('/courses/show/client', [CourseController::class, 'indexForUsers']);
        Route::get('/courses/getByCategories/client', [CourseController::class, 'getByCategories']);
        Route::post('/courses/{course}/view', [CourseController::class, 'recordView']);
        Route::post('/courses/user/{course}/like', [CourseController::class, 'toggleLike']);
        Route::post('courses/{course}/rate', [RatingController::class, 'store']);
        Route::post('ratings/{rating}', [RatingController::class, 'update']);
        Route::get('courses/{course}/my-rating', [RatingController::class, 'getUserRating']);
        Route::get('/courses/{id}/likes', [CourseController::class, 'getLikes']);


    // الإشعارات
      
    
    Route::get('/notifications/userall', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications/chat', [NotificationController::class, 'getChatNotifications']);
    Route::post('/notifications/read/all', [NotificationController::class, 'markAllAsRead']);

   //البحث
     Route::get('/search', [SearchController::class, 'search']);

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

    //Route::post('/animal-cases/immediate-request', [AnimalCaseController::class, 'immediateRequest']);


    Route::get('/volunteer/requests/employee', [VolunteerController::class, 'index']); // عرض طلبات التطوع
    Route::post('/volunteer/requests/{id}/respons', [VolunteerController::class, 'respond']); // الرد على طلب التطوع

    Route::get('/animal-cases/immediate', [StafController::class, 'listImmediateCases']);
    Route::get('/doctors', [StafController::class, 'availableDoctors']);
    Route::post('/appointments/schedule/{caseId}', [StafController::class, 'scheduleImmediate']);
   // Route::post('/employee/appointment/{id}/respond', [AppointmentController::class, 'respond']);
    Route::get('appointments/processed', [AppointmentController::class, 'getProcessedAppointments']);
    Route::get('appointments/status/{status}', [AppointmentController::class, 'getAppointmentsByStatus']);

    Route::get('/cases/regular', [StafController::class, 'listRegularCases']);//عرض الحالات العادية
    Route::get('/cases/immediate', [StafController::class, 'listImmediateCases']); //عرض الحالات الضرورية

    Route::post('/user/toggle-availability', [AuthController::class, 'toggleAvailability']);


    Route::get('/volunteer-types', [VolunteerTypeController::class, 'index']); // عرض جميع الأقسام
    Route::post('/volunteer-types/store', [VolunteerTypeController::class, 'store']); // إنشاء قسم جديد
    Route::get('/volunteer-types/{id}', [VolunteerTypeController::class, 'show']); // عرض قسم معين (يحتاج تعديل في الـ Controller)
    Route::put('/volunteer-types/{id}', [VolunteerTypeController::class, 'update']); // تحديث قسم
    Route::delete('volunteer-types/{id}', [VolunteerTypeController::class, 'destroy']); // حذف قسم
    Route::get('/volunteer/with-count', [VolunteerTypeController::class, 'indexWithCount']);
    Route::get('/volunteer-types/{id}/volunteers', [VolunteerTypeController::class, 'showVolunteers']);

    Route::get('/events/dashboard', [EventController::class, 'dashboard']);

    Route::get('/ambulances', [AmbulanceController::class, 'index']);
    Route::post('/ambulances', [AmbulanceController::class, 'store']);
    Route::put('/ambulances/{ambulance}', [AmbulanceController::class, 'update']);
    Route::delete('/ambulances/{ambulance}', [AmbulanceController::class, 'destroy']);

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
    Route::get('/admin/posts/approve', [AdminPostController::class, 'show_post_admin']);
    Route::get('/admin/adoptions', [AdminPostController::class, 'getAllAdoptionRequests']);
    Route::get('/admin/ads/pending', [AdminAd_midiaController::class, 'index']);
    Route::post('/admin/ads/{adId}/respond', [AdminAd_midiaController::class, 'respond']);
    Route::post('/donates/{id}/respond', [DonateController::class, 'respond']);
    Route::get('/donates', [DonateController::class, 'index']);
    Route::post('/temporary-care-requests/{requestId}/respond', [TemporaryCareController::class, 'respondToRequest']);
    Route::get('/temporary-care-requests', [TemporaryCareController::class, 'getAllRequests']);
});



Route::middleware(['auth:sanctum', 'role:vet'])->group(function () {
    Route::post('/doctor/courses', [CourseController::class, 'store']);
    // 📚 عرض كورسات الطبيب نفسه
    Route::get('/courses', [CourseController::class, 'indexForUsers']);
    //عرض الكورسات
    Route::get('/doctor/courses', [CourseController::class, 'indexForDoctor']);
    // 🗑️ حذف كورس يخص الطبيب (أو مسؤول)
    Route::delete('/doctor/courses/{id}', [CourseController::class, 'destroy']);
    Route::get('/doctor/courses/{course}/stats', [CourseController::class, 'getCourseStats']);
    Route::get('/doctor/stats', [CourseController::class, 'getDoctorStats']);//احصائيات كورس 
     Route::delete('admin/ratings/{rating}', [RatingController::class, 'destroy']);
   

    Route::post('/doctor/reports', [ReportController::class, 'store']);
    Route::get('/doctor/reports', [ReportController::class, 'index']);
    Route::get('/doctor/reports/{id}', [ReportController::class, 'show']);
    Route::post('/doctor/reports/{id}', [ReportController::class, 'update']);
    Route::delete('/doctor/reports/{id}', [ReportController::class, 'destroy']);
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus']);
    Route::get('/reports/search', [ReportController::class, 'search']);

   Route::get('/animal-cases/approved', [AnimalCaseController::class, 'getApprovedCases']); // عرض حالات الحيوانات
    Route::post('/appointments/request', [AppointmentController::class, 'request']); // طلب موعد
   Route::get('/doctor/appointments', [AppointmentController::class, 'getDoctorAppointments']);

    Route::post('/creat/vaccine', [VaccineController::class, 'store']);
    Route::get('/show/vaccine', [VaccineController::class, 'index']);
     Route::get('/show/vaccine/{id}', [VaccineController::class, 'show']);
    Route::post('/update/vaccine/{id}', [VaccineController::class, 'update']);
    Route::delete('/delete/vaccine/{id}', [VaccineController::class, 'destroy']);
  Route::post('/update/vaccine-image/{id}', [VaccineController::class, 'updateImage']);

    Route::post('/cases/{case}/approve', [AnimalCaseController::class, 'approve']);

    Route::get('/notifications/doctor', [VaccineController::class, 'notifications']);
    Route::get('/notifications/unread', [VaccineController::class, 'unreadNotifications']);
    Route::post('/notifications/read-all', [VaccineController::class, 'markAllRead']);
    Route::post('/notifications/{id}/mark-as-read', [VaccineController::class, 'markAsReadById']);
    Route::get('/doctor/my-profile', [AuthController::class, 'showCurrentDoctorProfile']);
     Route::post('/doctor/profile', [AuthController::class, 'updateDoctorProfile']);

      Route::get('/doctor/catogries', [CourseController::class, 'getCategories']);
});

Route::get('/animal-cases/doctor', [AnimalCaseController::class, 'index'])
     ->middleware(['auth:sanctum', 'role:vet']);

Route::middleware('auth:sanctum')->get('/my-cases', [AnimalCaseController::class, 'myApprovedCases']);












Route::middleware('auth:sanctum')->group(function () {
    Route::get('/events/user/', [AdminController::class, 'listActiveEvents']);
    Route::post('/events/{eventId}/register', [AdminController::class, 'registerForEvent']);
});








///////////////////////////////////fromyaser
// مسارات المدير
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // إدارة المستخدمين
     Route::get('/users/all', [AdminController::class, 'getUsers']);
        Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);
        Route::post('/users/{user}/change-role', [AdminController::class, 'changeUserRole']);
       Route::post('/admin/users/add', [UserController::class, 'store']);
    // إدارة الخدمات
    Route::get('/admin/services', [AdminController::class, 'getServices']);
    Route::post('/admin/services', [AdminController::class, 'addService']);
    Route::put('/admin/services/{serviceId}', [AdminController::class, 'updateService']);
    Route::delete('/admin/services/{serviceId}', [AdminController::class, 'deleteService']);

    // // التقارير
    Route::get('/admin/reports/performance', [AdminController::class, 'getPerformanceReport']);
    Route::get('/admin/reports/daily', [AdminController::class, 'getDailyReport']);
  Route::get('/export-ads', [FinancialReportController::class, 'exportAds']);   //تقرير مالي
    // // مسارات إدارة الفعاليات للمدير
        Route::get('/events', [AdminController::class, 'getEvents']);
        Route::post('/events', [AdminController::class, 'createEvent']);
        Route::put('/events/{eventId}', [AdminController::class, 'updateEvent']);
        Route::delete('/events/{eventId}', [AdminController::class, 'deleteEvent']);
        Route::get('/events/{eventId}/participants', [AdminController::class, 'getEventParticipants']);
        Route::put('/events/{eventId}/participants/{participantId}', [AdminController::class, 'updateParticipantStatus']);

        Route::get('/admin/processed-requests', [RequestController::class, 'getProcessedRequests']);
});

    Route::middleware(['auth:sanctum'])->get('/animals', [EmployeeController::class, 'index']);         // عرض كل الحيوانات

// مسارات الموظف
Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {

    // التقارير
    Route::get('/employee/reports/daily', [EmployeeController::class, 'getDailyReport']);
    // مسارات الفعاليات للموظفين
    Route::get('/employee/events', [EmployeeController::class, 'getAvailableEvents']);
    Route::post('/employee/events/{eventId}/register', [EmployeeController::class, 'registerForEvent']);
    Route::post('/employee/events/{eventId}/cancel', [EmployeeController::class, 'cancelEventRegistration']);
    Route::get('/employee/my-events', [EmployeeController::class, 'getMyEvents']);

       Route::get('/stats/general', [StatisticsController::class, 'generalStats']);
    Route::get('/stats/general/appointments', [StatisticsController::class, 'appointmentStats']);
    Route::get('/stats/general/animal-cases', [StatisticsController::class, 'animalCaseStats']);
    Route::get('/stats/general/adoption-care', [StatisticsController::class, 'adoptionCareStats']);
});

// مسارات إدارة الحيوانات
Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
    // Animal Management
    Route::get('/animals/{id}', [EmployeeController::class, 'show']);       // عرض حيوان محدد
    Route::get('/animals/available', [EmployeeController::class, 'getAvailableAnimals']); // الحيوانات المتاحة
    Route::post('/animals', [EmployeeController::class, 'store']);          // إضافة حيوان
Route::post('/animals/{animal}/update', [EmployeeController::class, 'update']);   
 //Route::patch('/animals/{id}/availability', [EmployeeController::class, 'updateAvailability']); // تحديث حالة التوفر
    Route::delete('/animals/{id}', [EmployeeController::class, 'destroy']); // حذف حيوان
    Route::post('/animals/upload-image', [EmployeeController::class, 'uploadImage']);
    Route::patch('/animals/{id}/update-purpose', [EmployeeController::class, 'updatePurpose']);
    Route::patch('/animals/{animal}/status', [EmployeeController::class, 'updatestatusavail']);
    Route::get('/animals/adoptions/filter', [EmployeeController::class, 'adoptions']);
    Route::get('/animals/temporary/filter', [EmployeeController::class, 'temporaryCare']);
    Route::get('/animal/filter/purpos', [EmployeeController::class, 'filte_index']);
    // Animal Types Management
    Route::get('/animal-types', [EmployeeController::class, 'getAllTypes']); // الحصول على كل الأنواع
});


////////////////////////////managepost////////////////////////
Route::middleware(['auth:sanctum', 'role:admin|employee'])->group(function () {

        // إدارة البوستات
        Route::post('/official-posts', [PolicyPostController::class, 'storeOfficial']);
        Route::delete('/posts/{post}', [PolicyPostController::class, 'forceDestroy']);
        Route::delete('/posts/{post}/force-delete', [PolicyPostController::class, 'forceDestroy']);
        Route::get('/official-posts', [PolicyPostController::class, 'getOfficialPosts']);
        
        // إدارة التعليقات
        Route::post('/comments', [PolicyCommentController::class, 'store']);
        Route::delete('/comments/{comment}/delete', [PolicyCommentController::class, 'forceDestroy']);
        Route::post('/comments/{comment}/replyadmin', [PolicyCommentController::class, 'reply']);
        Route::get('/posts/{post}/commentadmin', [PolicyCommentController::class, 'getCommentsWithReplies']);

});
