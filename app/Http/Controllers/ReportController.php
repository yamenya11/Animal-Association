<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Models\Report;
class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * إنشاء تقرير جديد
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'animal_name' => 'required|string|max:255',
            'animal_age' => 'required|integer',
            'animal_weight' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'status' => 'nullable|in:Pending,Completed,Canceled',
            'temperature' => 'nullable|string',
            'pluse' => 'nullable|string',
            'respiration' => 'nullable|string',
            'general_condition' => 'nullable|string',
            'midical_separated' => 'nullable|string',
            'note' => 'required|string'
        ]);

        $validatedData['doctor_id'] = auth()->id();
        try {
            $report = $this->reportService->createReport($validatedData);

            return response()->json([
                'success' => true,
                'data' => $report,
                'image_url' => $report->image_url,
                'message' => 'تم إنشاء التقرير بنجاح'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء التقرير: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض جميع التقارير
     */
    public function index()
    {
        $reports = $this->reportService->getAllReports();

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * عرض تقرير محدد
     */
    public function show($id)
    {
        try {
            $report = $this->reportService->getReport($id);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'التقرير غير موجود'
            ], 404);
        }
    }

    /**
     * تحديث التقرير
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'animal_name' => 'sometimes|string|max:255',
            'animal_age' => 'sometimes|integer',
            'animal_weight' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|in:Pending,Completed,Canceled',
            'temperature' => 'nullable|string',
            'pluse' => 'nullable|string',
            'respiration' => 'nullable|string',
            'general_condition' => 'nullable|string',
            'midical_separated' => 'nullable|string',
            'note' => 'sometimes|string'
        ]);

        try {
            $report = $this->reportService->updateReport($id, $validatedData);

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'تم تحديث التقرير بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث التقرير: ' . $e->getMessage()
            ], 500);
        }
    }

  
    public function destroy($id)
    {
        try {
            $this->reportService->deleteReport($id);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التقرير بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف التقرير: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:Pending,Completed,Canceled'
    ]);

    try {
        $report = Report::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة التقرير بنجاح',
           // 'data' => $report
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'فشل تحديث حالة التقرير: ' . $e->getMessage()
        ], 500);
    }
}
public function search(Request $request)
{
    $query = Report::query();
    
    // فلترة حسب حالة التقرير
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }
    
    // بحث حسب اسم الحيوان
    if ($request->has('animal_name')) {
        $query->where('animal_name', 'like', '%'.$request->animal_name.'%');
    }
    
    // فلترة حسب التاريخ
    if ($request->has('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $reports = $query->latest()->paginate(10);

    return response()->json([
        'success' => true,
        'data' => $reports
    ]);
}
}