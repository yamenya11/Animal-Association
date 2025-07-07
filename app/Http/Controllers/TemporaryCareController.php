<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TemporaryCareService;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryCareRequest;
class TemporaryCareController extends Controller
{
      protected $temporaryCareService;

    public function __construct(TemporaryCareService $temporaryCareService)
    {
        $this->temporaryCareService = $temporaryCareService;
    }

     public function createRequest(Request $request)
{
    try {
        $response = $this->temporaryCareService->createRequest($request);
        return response()->json($response, $response['code']);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => false,
            'message' => 'الحيوان غير متاح للرعاية المؤقتة'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء تقديم الطلب: ' . $e->getMessage()
        ], 500);
    }
}
   public function getUserRequests()
    {
        return response()->json([
            'status' => true,
            'data' => $this->temporaryCareService->getUserRequests()
        ]);
    }

    public function getAvailableAnimals()
    {
        return response()->json([
            'status' => true,
            'data' => $this->temporaryCareService->getAvailableAnimals()
        ]);
    }

    public function getAllRequests()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بهذه العملية'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $this->temporaryCareService->getAllRequests()
        ]);
    }


     public function respondToRequest(Request $request, $requestId)
    {
        // التحقق من صلاحيات المسؤول
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بهذه العملية'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $response = $this->temporaryCareService->respondToRequest($requestId, $validated['status']);
        
        return response()->json($response, $response['code'] ?? 200);
    }
//     public function getRequestDetails(Request $request, $id)
// {
//     // استرجاع تفاصيل الطلب
//     $requestDetails = TemporaryCareRequest::findOrFail($id);
    
//     // يمكنك إضافة المزيد من المنطق هنا حسب احتياجاتك
//     return response()->json([
//         'status' => true,
//         'data' => $requestDetails,
//         'additional_info' => 'أي معلومات إضافية'
//     ]);
// }
}
