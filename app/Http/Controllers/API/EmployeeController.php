<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    // إدارة المستخدمين
    public function getUsers(): JsonResponse
    {
        $users = $this->employeeService->getAllUsers();
        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    // إدارة المحتوى
    public function getPendingContent(): JsonResponse
    {
        $content = $this->employeeService->getPendingContent();
        return response()->json([
            'status' => true,
            'data' => $content
        ]);
    }

    public function approveContent(Request $request, $contentId): JsonResponse
    {
        $response = $this->employeeService->approveContent($contentId, $request->notes);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    public function rejectContent(Request $request, $contentId): JsonResponse
    {
        $response = $this->employeeService->rejectContent($contentId, $request->notes);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    // التقارير
    public function getDailyReport(): JsonResponse
    {
        $report = $this->employeeService->generateDailyReport();
        return response()->json([
            'status' => true,
            'data' => $report
        ]);
    }

    // التواصل مع المتطوعين
    public function getVolunteers(): JsonResponse
    {
        $volunteers = $this->employeeService->getActiveVolunteers();
        return response()->json([
            'status' => true,
            'data' => $volunteers
        ]);
    }

    public function sendMessageToVolunteer(Request $request, $volunteerId): JsonResponse
    {
        $response = $this->employeeService->sendMessageToVolunteer($volunteerId, $request->message);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    // إدارة الفعاليات
    public function getAvailableEvents(): JsonResponse
    {
        $events = Event::where('status', 'active')
            ->where('start_date', '>', now())
            ->with(['creator', 'participants'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    public function registerForEvent($eventId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);

            // التحقق من حالة الفعالية
            if ($event->status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'الفعالية غير متاحة للتسجيل'
                ], 400);
            }

            // التحقق من عدد المشاركين
            if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، الفعالية مكتملة العدد'
                ], 400);
            }

            // التحقق من عدم التسجيل مسبقاً
            $existingRegistration = EventParticipant::where('event_id', $eventId)
                ->where('user_id', auth()->id())
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'status' => false,
                    'message' => 'أنت مسجل بالفعل في هذه الفعالية'
                ], 400);
            }

            // التسجيل في الفعالية
            $participant = EventParticipant::create([
                'event_id' => $eventId,
                'user_id' => auth()->id(),
                'status' => 'registered'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم التسجيل في الفعالية بنجاح',
                'data' => $participant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التسجيل في الفعالية',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function cancelEventRegistration($eventId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $registration = EventParticipant::where('event_id', $eventId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $registration->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم إلغاء التسجيل في الفعالية بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إلغاء التسجيل في الفعالية',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getMyEvents(): JsonResponse
    {
        $events = EventParticipant::with(['event.creator'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }
} 