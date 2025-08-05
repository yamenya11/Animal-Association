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

    public function processedRequests()
{
    return response()->json([
        'status' => true,
        'data' => $this->temporaryCareService->getProcessedRequests()
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
        
    

        return response()->json([
            'status' => true,
            'data' => $this->temporaryCareService->getAllRequests()
        ]);
    }


   public function respondToRequest(Request $request, $requestId)
{
    try {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $response = $this->temporaryCareService->respondToRequest($requestId, $validated['status']);

        if (!$response['status']) {
            return response()->json([
                'status' => false,
                'message' => $response['message'] ?? 'فشل في معالجة الطلب',
                'errors' => $response['errors'] ?? null,
                'code' => $response['code'] ?? 400
            ], $response['code'] ?? 400);
        }

        return response()->json([
            'status' => true,
            'message' => $validated['status'] === 'approved' 
                ? 'تمت الموافقة على الطلب بنجاح' 
                : 'تم رفض الطلب',
            'data' => $response['data'] ?? null,
            'code' => 200
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ غير متوقع',
            'errors' => ['system' => $e->getMessage()],
            'code' => 500
        ], 500);
    }
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
