<?php

namespace App\Services;

use App\Models\User;
use App\Models\Service;
use App\Models\Event;
use App\Models\Donate;
use App\Models\AdMedia;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;


class AdminService
{


public function changeUserRole(User $user, string $role): array
{
    if (!Role::where('name', $role)->exists()) {
        return [
            'success' => false,
            'message' => 'الدور المحدد غير موجود في النظام'
        ];
    }

    $user->syncRoles([$role]);
    
    return [
        'success' => true,
        'message' => 'تم تغيير دور المستخدم بنجاح',
        'data' => [
            'user_id' => $user->id,
            'new_role' => $role,
            'user_roles' => $user->getRoleNames()->toArray()
        ]
    ];
}

public function getAllUsers()
{
    return User::with(['roles:id,name', 'volunteerRequest'])
        ->select([
            'id',
            'name',
            'email',
            'created_at',
            'wallet_balance',
            'level',
            'address',
            'profile_image',
            'phone'
        ])
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'wallet_balance' => $user->wallet_balance,
                'level' => $user->level,
                'address' => $user->address,
                'profile_image' => $user->profile_image ? config('app.url') . '/storage/' . $user->profile_image : null,
                'phone' => $user->phone,
                'roles' => $user->roles->pluck('name'),
                'volunteer_specialization' => $user->volunteerRequest ? $this->getVolunteerTypeName($user->volunteerRequest->volunteer_type) : null,
                'volunteer_status' => $user->volunteerRequest ? $user->volunteerRequest->status : null
            ];
        });
}

// دالة مساعدة لتحويل volunteer_type إلى نص مقروء
protected function getVolunteerTypeName($type)
{
    $types = [
        'cleaning_shelters' => 'تنظيف الملاجئ',
        'animal_care' => 'رعاية الحيوانات',
        'photography_and_documentation' => 'التصوير والتوثيق',
        'design_and_markiting' => 'التصميم والتسويق',
        'social_midea_administrator' => 'إدارة السوشيال ميديا',
        'school_awareness' => 'التوعية المدرسية'
    ];
    
    return $types[$type] ?? $type;
}



public function updateUserAsAdmin(Request $request, $userId): array
{
    $user = User::findOrFail($userId);

    $validated = $request->validate([
        'name'     => 'sometimes|string|max:255',
        'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($userId)],
        'phone'    => ['sometimes', 'digits:10', Rule::unique('users')->ignore($userId)],
        'password' => 'sometimes|string|min:6',
        'level'    => 'sometimes|string|max:255',
        'address'  => 'sometimes|string|max:255',
    ]);

    if (isset($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    }

    $user->update($validated);

    return [
        'status' => true,
        'message' => 'تم تحديث بيانات المستخدم من قبل المسؤول بنجاح',
        'data' => $user,
    ];
}

    public function deleteUser($userId): array
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return [
            'status' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ];
    }

    // // إدارة الخدمات
    // public function getAllServices()
    // {
    //     return Service::all();
    // }

   

  public function generatePerformanceReport()
{
    $report = [
        'total_users'        => User::count(),
        'total_adoptions'    => DB::table('adoptions')->count(),
        'total_appointments' => DB::table('appointments')->count(),
        'total_volunteers'   => DB::table('volunteer_requests')
                                    ->where('status', 'approved')
                                    ->count(),
        'total_donations'    => DB::table('donates')->count(),
        'total_ads_requests' => DB::table('ad_media')->count(),

        // إذا كنت تريد جمع قيمة التبرعات:
        'total_donation_amount' => DB::table('donates')
            ->where('status', 'approved')
            ->where('donation_type', 'money')
            ->sum('amount'),

        // مجموع الطعام بالكيلو فقط (المقبولة)
        'total_food_donations' => DB::table('donates')
            ->where('status', 'approved')
            ->where('donation_type', 'food')
            ->sum(DB::raw("CAST(ammountinkello AS UNSIGNED)")),
    ];

    return $report;
}


        public function generateDailyReport()
        {
            $today = now()->toDateString();

            $report = [
                'new_users' => DB::table('users')
                    ->whereDate('created_at', $today)
                    ->count(),

                'new_adoptions' => DB::table('adoptions')
                    ->whereDate('created_at', $today)
                    ->count(),

                'new_appointments' => DB::table('appointments')
                    ->whereDate('created_at', $today)
                    ->count(),

                'new_volunteers' => DB::table('volunteer_requests')
                    ->where('status', 'approved')
                    ->whereDate('created_at', $today)
                    ->count(),

                'new_donations' => DB::table('donates')
                    ->whereDate('created_at', $today)
                    ->count(),

                // مجموع التبرعات المالية المقبولة اليوم
                'donation_amount_today' => DB::table('donates')
                    ->whereDate('created_at', $today)
                    ->where('status', 'approved')
                    ->where('donation_type', 'money')
                    ->sum('amount'),

                // مجموع التبرعات الغذائية بالكيلو المقبولة اليوم
                'donation_food_today' => DB::table('donates')
                    ->whereDate('created_at', $today)
                    ->where('status', 'approved')
                    ->where('donation_type', 'food')
                    ->sum(DB::raw("CAST(ammountinkello AS UNSIGNED)")),

                'new_ads_requests' => DB::table('ad_media')
                    ->whereDate('created_at', $today)
                    ->count(),
            ];

            return [
                'status' => true,
                'data' => $report,
            ];
        }


    // إدارة الفعاليات
        public function getAllEvents()
        {
            $events = Event::with(['creator', 'participants.user'])
                ->latest()
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'start_date' => \Carbon\Carbon::parse($event->start_date)->format('Y-m-d H:i'),
                        'end_date' => \Carbon\Carbon::parse($event->end_date)->format('Y-m-d H:i'),
                        'location' => $event->location,
                        'status' => $this->getStatusDisplay($event->status),
                        'creator' => $event->creator->name ?? 'غير معروف',
                        'participants_count' => $event->participants->count()
                    ];
                });

            return response()->json([
                'status' => true,
                'events' => $events
            ]);
        }

protected function getStatusDisplay($status)
{
    return match ($status) {
        'pending' => 'قيد الانتظار',
        'active' => 'نشط',
        'completed' => 'منتهي',
        default => $status
    };
}


public function createEvent(Request $request): array
{
    $validated = $request->validate([
        'title'            => 'required|string|max:255',
        'description'      => 'required|string',
        'start_date'       => 'required|date',
        'end_date'         => 'required|date|after:start_date',
        'location'         => 'required|string',
        'max_participants' => 'nullable|integer|min:1',
    ]);

    $user = auth()->user();
    $validated['created_by'] = $user->id;

    // تفعيل الحدث فورًا إذا كان الأدمن
    $validated['status'] = $user->hasRole('admin') ? 'active' : 'pending';

    $event = Event::create($validated);

    // إضافة منشئ الحدث كمشارك بشكل تلقائي (مرة واحدة فقط)
    EventParticipant::firstOrCreate([
        'event_id' => $event->id,
        'user_id'  => $user->id,
    ], [
        'status' => 'registered',
        'notes'  => 'منشئ الحدث',
    ]);

    // إرجاع الحدث مع المشاركين
    $event->load('participants.user');

    return [
        'status'  => true,
        'message' => 'تم إنشاء الفعالية بنجاح',
        'data'    => $event,
    ];
}







  public function updateEvent(Request $request, $eventId): array
{
    try {
        DB::beginTransaction();

        $event = Event::findOrFail($eventId);

        $validated = $request->validate([
            'title'            => 'sometimes|string|max:255',
            'description'      => 'sometimes|string',
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date|after:start_date',
            'location'         => 'sometimes|string',
            'max_participants' => 'nullable|integer|min:1',
            'status'           => 'sometimes|in:pending,active,completed,cancelled'
        ]);

        $event->update($validated);

        $userId = Auth::id();

        // إضافة المستخدم كمشارك إذا لم يكن موجودًا بالفعل
        EventParticipant::firstOrCreate([
            'event_id' => $event->id,
            'user_id'  => $userId,
        ], [
            'status' => 'registered',
            'notes'  => 'تم الانضمام عند التحديث',
        ]);

        DB::commit();

        return [
            'status'  => true,
            'message' => 'تم تحديث الفعالية بنجاح',
            'data'    => $event->load('participants.user')
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        return [
            'status'  => false,
            'message' => 'حدث خطأ أثناء تحديث الفعالية',
            'error'   => $e->getMessage()
        ];
    }
}


    public function deleteEvent($eventId): array
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);
            $event->delete();

            DB::commit();

            return [
                'status' => true,
                'message' => 'تم حذف الفعالية بنجاح'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف الفعالية',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getEventParticipants($eventId)
    {
        return EventParticipant::with('user')
            ->where('event_id', $eventId)
            ->get();
    }

    public function updateParticipantStatus($eventId, $participantId, $status): array
    {
        try {
            DB::beginTransaction();

            $participant = EventParticipant::where('event_id', $eventId)
                ->where('id', $participantId)
                ->firstOrFail();

            $participant->update([
                'status' => $status
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'تم تحديث حالة المشارك بنجاح',
                'data' => $participant
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة المشارك',
                'error' => $e->getMessage()
            ];
        }
    }
} 