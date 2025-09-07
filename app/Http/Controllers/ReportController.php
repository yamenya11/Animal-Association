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
            'animal_case_id' => 'required|exists:animal_cases,id',
            'animal_name' => 'nullable|string|max:255',
            'animal_age' => 'nullable|integer',
            'animal_weight' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'status' => 'nullable|in:Pending,Completed,Canceled',
            'temperature' => 'nullable|string',
            'pluse' => 'nullable|string',
            'respiration' => 'nullable|string',
            'general_condition' => 'nullable|string',
            'medical_separated' => 'nullable|string',
            'note' => 'required|string',
        ]);

        $validatedData['doctor_id'] = auth()->id();

        try {
            $report = $this->reportService->createReport($validatedData);

            // رابط الصورة للحيوان المرتبط
            if ($report->animal && $report->animal->image) {
                $report->animal->image_url = config('app.url') . '/storage/' . $report->animal->image;
            }

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

 public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'animal_case_id' => 'sometimes|integer|exists:animal_cases,id',
            'animal_name' => 'sometimes|string|max:255',
            'animal_age' => 'sometimes|integer',
            'animal_weight' => 'sometimes|string|max:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
            'status' => 'sometimes|in:Pending,Completed,Canceled',
            'temperature' => 'nullable|string',
            'pluse' => 'nullable|string',
            'respiration' => 'nullable|string',
            'general_condition' => 'nullable|string',
            'medical_separated' => 'nullable|string',
            'note' => 'sometimes|string',
        ]);

        try {
            $report = $this->reportService->updateReport($id, $validatedData);

            $report->image_url = $report->image ? config('app.url') . '/storage/' . $report->image : null;
            if ($report->animal && $report->animal->image) {
                $report->animal->image_url = config('app.url') . '/storage/' . $report->animal->image;
            }

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



    /**
     * عرض جميع التقارير
     */
   public function index()
    {
        $reports = $this->reportService->getAllReportsWithAnimal();

        // معالجة روابط الصور
        $reports->each(function ($report) {
            $report->image_url = $report->image ? config('app.url') . '/storage/' . $report->image : null;
            if ($report->animal && $report->animal->image) {
                $report->animal->image_url = config('app.url') . '/storage/' . $report->animal->image;
            }
        });

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
            $report = $this->reportService->getReportWithAnimal($id);

            $report->image_url = $report->image ? config('app.url') . '/storage/' . $report->image : null;
            if ($report->animal && $report->animal->image) {
                $report->animal->image_url = config('app.url') . '/storage/' . $report->animal->image;
            }

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
        $query = Report::with('animalCase');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('animal_name')) {
            $query->where('animal_name', 'like', '%'.$request->animal_name.'%');
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $reports = $query->latest()->paginate(10);

        // روابط الصور
        $reports->getCollection()->transform(function ($report) {
            $report->image_url = $report->image ? config('app.url') . '/storage/' . $report->image : null;
            if ($report->animal && $report->animal->image) {
                $report->animal->image_url = config('app.url') . '/storage/' . $report->animal->image;
            }
            return $report;
        });

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

}