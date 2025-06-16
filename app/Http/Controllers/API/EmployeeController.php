<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;

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
} 