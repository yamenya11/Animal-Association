<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnimalCaseService;
use Illuminate\Http\JsonResponse;
use App\Models\AnimalCase;
use Illuminate\Support\Facades\Auth;
class AnimalCaseController extends Controller
{
 protected $animalCaseService;

    public function __construct(AnimalCaseService $animalCaseService)
    {
        $this->animalCaseService = $animalCaseService;
    }

    public function store(Request $request)
    {
        $response = $this->animalCaseService->createCase($request);

        return response()->json($response, $response['status'] ? 201 : 400);
    }

    // جلب الحالات الخاصة بالمستخدم
    public function index(): JsonResponse
    {
        $cases = $this->animalCaseService->getAnimalCasesByUser();

        return response()->json([
            'status' => true,
            'data' => $cases,
        ]);
    }
//عرض الحالات العادية بعد الموافقة
    public function getApprovedCases(): JsonResponse
{
    $approvedCases = $this->animalCaseService->getApprovedCases();
    
    return response()->json([
        'status' => true,
        'data' => $approvedCases
    ]);
}
public function approve(Request $request, $caseId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
          
        ]);

        $case = AnimalCase::findOrFail($caseId);

        $case->update([
            'approval_status' => $request->status,
            'doctor_id' => Auth::id()
           // 'approved_by' => Auth::id(),
            //'approved_at' => now(),
           // 'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الموافقة بنجاح',
            'data' => $case
        ]);
    }
}
