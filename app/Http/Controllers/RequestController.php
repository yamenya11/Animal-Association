<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Ad;
use \App\Models\Adoption;
use \App\Models\Donate;
use \App\Models\TemporaryCareRequest;
use Illuminate\Http\JsonResponse; 
class RequestController extends Controller
{

 public function getProcessedRequests(): JsonResponse
{
    // جلب الإعلانات المعالجة (كما هي)
    $ads = Ad::whereIn('status', ['approved', 'rejected'])
        ->with('user:id,name')
        ->get();

    // جلب طلبات التبني المعالجة (كما هي)
    $adoptions = Adoption::whereIn('status', ['approved', 'rejected'])
        ->with(['user:id,name', 'animal:id,name'])
        ->get();

    // جلب التبرعات (المقبولة والمرفوضة) - التعديل هنا فقط
    $donations = Donate::with('user:id,name')
        ->get()
        ->map(function ($donation) {
            return [
                'id' => $donation->id,
                'user_id' => $donation->user_id,
                'amount' => $donation->amount,
                'is_approved' => $donation->is_approved,
                'status' => $donation->is_approved ? 'approved' : 'rejected', // إضافة حقل مشتق
                'user' => $donation->user,
                'created_at' => $donation->created_at,
                'updated_at' => $donation->updated_at
                // باقي الحقول الأصلية...
            ];
        });

    // جلب طلبات الرعاية المؤقتة المعالجة (كما هي)
    $careRequests = TemporaryCareRequest::whereIn('status', ['approved', 'rejected'])
        ->with(['user:id,name', 'animal:id,name'])
        ->get();

    return response()->json([
        'status' => true,
        'data' => [
            'ads' => $ads,
            'adoptions' => $adoptions,
            'donations' => $donations, // الآن تحتوي على جميع التبرعات
            'temporary_care_requests' => $careRequests,
        ]
    ]);
}

}
