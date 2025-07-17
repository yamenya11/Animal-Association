<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function createReport(array $data)
    {
        // معالجة رفع الصورة إذا وجدت
        if (isset($data['image'])) {
            $imagePath = $data['image']->store('reports/images', 'public');
            $data['image'] = $imagePath;
        }

        // تصحيح أسماء الحقول إذا لزم الأمر
        $correctedData = [
            'animal_name' => $data['animal_name'],
            'animal_age' => $data['animal_age'],
            'animal_weight' => $data['animal_weight'],
            'image' => $data['image'] ?? null,
            'status' => $data['status'] ?? 'Pending',
            'temperature' => $data['temperature'] ?? null,
            'pluse' => $data['pluse'] ?? null, // تصحيح من pluse إلى pulse
            'respiration' => $data['respiration'] ?? null,
            'general_condition' => $data['general_condition'] ?? null, // إزالة المسافة
            'midical_separated' => $data['midical_separated'] ?? null, // تصحيح إملائي
            'note' => $data['note'],
            'doctor_id' => $data['doctor_id'] 
        ];

          if (!isset($correctedData['doctor_id'])) {
        throw new \Exception('لم يتم تحديد الطبيب المعالج');
    }

        return Report::create($correctedData);
    }

    public function updateReport($id, array $data)
    {
        $report = Report::findOrFail($id);

        if (isset($data['image'])) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($report->image) {
                Storage::disk('public')->delete($report->image);
            }
            $imagePath = $data['image']->store('reports/images', 'public');
            $data['image'] = $imagePath;
        }

        $report->update($data);
        return $report;
    }

    public function getReport($id)
    {
        return Report::findOrFail($id);
    }

    public function getAllReports()
    {
        return Report::all();
    }

    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        
        if ($report->image) {
            Storage::disk('public')->delete($report->image);
        }
        
        return $report->delete();
    }
}