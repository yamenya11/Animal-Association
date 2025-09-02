<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Animal;
use Illuminate\Support\Facades\Storage;

class ReportService
{
public function createReport(array $data): Report
{
    // التحقق من وجود animal_id وربطه بالحيوان
    if (empty($data['animal_id'])) {
        throw new \Exception('يجب تحديد الحيوان.');
    }

    $animal = Animal::find($data['animal_id']);
    if (!$animal) {
        throw new \Exception('الحيوان غير موجود.');
    }

    // استخدام بيانات الحيوان كافتراضية إذا لم تُرسل
    $data['animal_name'] = $data['animal_name'] ?? $animal->name;
    $data['animal_age']  = $data['animal_age'] ?? $this->calculateAge($animal->birth_date);

    // التحقق من وجود doctor_id
    if (empty($data['doctor_id'])) {
        throw new \Exception('لم يتم تحديد الطبيب المعالج.');
    }

    // رفع الصورة إذا وُجدت
    if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
        $data['image'] = $data['image']->store('reports/images', 'public');
    }

    // تصحيح أسماء الحقول وإعداد بيانات التقرير
    $reportData = [
        'animal_id'         => $data['animal_id'],
        'animal_name'       => $data['animal_name'],
        'animal_age'        => $data['animal_age'],
        'animal_weight'     => $data['animal_weight'] ?? null,
        'image'             => $data['image'] ?? null,
        'status'            => $data['status'] ?? 'Pending',
        'temperature'       => $data['temperature'] ?? null,
        'pulse'             => $data['pulse'] ?? null,
        'respiration'       => $data['respiration'] ?? null,
        'general_condition' => $data['general_condition'] ?? null,
        'medical_separated' => $data['medical_separated'] ?? null,
        'note'              => $data['note'] ?? null,
        'doctor_id'         => $data['doctor_id'],
    ];

    // إنشاء التقرير
    return Report::create($reportData);
}


protected function calculateAge($birthDate)
{
    if (!$birthDate) return null;
    
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birth);
    
    return $age->y; // العمر بالسنين
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