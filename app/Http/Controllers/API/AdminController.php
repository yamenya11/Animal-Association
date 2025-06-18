<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    // إدارة المستخدمين
    public function getUsers(): JsonResponse
    {
        $users = $this->adminService->getAllUsers();
        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    public function updateUser(Request $request, $userId): JsonResponse
    {
        $response = $this->adminService->updateUser($request, $userId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    public function deleteUser($userId): JsonResponse
    {
        $response = $this->adminService->deleteUser($userId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    // إدارة الخدمات
    public function getServices(): JsonResponse
    {
        $services = $this->adminService->getAllServices();
        return response()->json([
            'status' => true,
            'data' => $services
        ]);
    }

    public function addService(Request $request): JsonResponse
    {
        $response = $this->adminService->createService($request);
        return response()->json($response, $response['status'] ? 201 : 400);
    }

    public function updateService(Request $request, $serviceId): JsonResponse
    {
        $response = $this->adminService->updateService($request, $serviceId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    public function deleteService($serviceId): JsonResponse
    {
        $response = $this->adminService->deleteService($serviceId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    // التقارير
    public function getPerformanceReport(): JsonResponse
    {
        $report = $this->adminService->generatePerformanceReport();
        return response()->json([
            'status' => true,
            'data' => $report
        ]);
    }

    public function getDailyReport(): JsonResponse
    {
        $report = $this->adminService->generateDailyReport();
        return response()->json([
            'status' => true,
            'data' => $report
        ]);
    }

    // إدارة الفعاليات
    public function getEvents(): JsonResponse
    {
        $events = $this->adminService->getAllEvents();
        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    public function createEvent(Request $request): JsonResponse
    {
        $response = $this->adminService->createEvent($request);
        return response()->json($response, $response['status'] ? 201 : 400);
    }

    public function updateEvent(Request $request, $eventId): JsonResponse
    {
        $response = $this->adminService->updateEvent($request, $eventId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    public function deleteEvent($eventId): JsonResponse
    {
        $response = $this->adminService->deleteEvent($eventId);
        return response()->json($response, $response['status'] ? 200 : 400);
    }

    public function getEventParticipants($eventId): JsonResponse
    {
        $participants = $this->adminService->getEventParticipants($eventId);
        return response()->json([
            'status' => true,
            'data' => $participants
        ]);
    }

    public function updateParticipantStatus(Request $request, $eventId, $participantId): JsonResponse
    {
        $response = $this->adminService->updateParticipantStatus($eventId, $participantId, $request->status);
        return response()->json($response, $response['status'] ? 200 : 400);
    }
} 