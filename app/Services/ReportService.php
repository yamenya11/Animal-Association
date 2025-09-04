<?php

namespace App\Services;

use App\Models\Report;
use App\Models\AnimalCase;
use Illuminate\Support\Facades\Storage;

class ReportService
{
public function createReport(array $data): Report
{
    // التحقق من وجود animal_id وربطه بالحيوان
    if (empty($data['animal_case_id'])) {
        throw new \Exception('يجب تحديد حالة الحيوان.');
    }

    $animal = AnimalCase::find($data['animal_case_id']);
    if (!$animal) {
        throw new \Exception('الحالة غير موجود.');
    }

    // استخدام بيانات الحيوان كافتراضية إذا لم تُرسل
    $data['animal_name'] = $data['animal_name'] ?? $animal->name;
    $data['animal_age']  = $data['animal_age'] ?? $this->calculateAge($animal->birth_date);

    if (empty($data['doctor_id'])) {
        throw new \Exception('لم يتم تحديد الطبيب المعالج.');
    }

    if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
        $data['image'] = $data['image']->store('reports/images', 'public');
    }

    $reportData = [
        'animal_case_id'         => $data['animal_case_id'],
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

    $report = Report::create($reportData);

    // إضافة رابط الصورة مباشرة
    $report->image_url = $report->image ? config('app.url') . '/storage/' . $report->image : null;

    return $report;
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
        if ($report->image) {
            Storage::disk('public')->delete($report->image);
        }
        $data['image'] = $data['image']->store('reports/images', 'public');
    }

    $report->update($data);

    // إضافة رابط الصورة مباشرة
   $report->image_url = $report->image 
    ? config('app.url') . '/storage/' . $report->image 
    : null;

    return $report;
}

  public function getAllReportsWithAnimal()
    {
        // تحميل التقارير مع الحيوان المرتبط
        return Report::with('animal')->get();
    }

    public function getReportWithAnimal($id)
    {
        return Report::with('animal')->findOrFail($id);
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