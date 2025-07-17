<?php

namespace App\Services;

use App\Models\VolunteerType;
use App\Models\User;
use App\Models\VolunteerRequest;
class VolunteerTypeService
{
    public function getAllTypes()
    {
      return VolunteerType::all();
    }

    public function createType(array $data)
    {
        return VolunteerType::create($data);
    }

    public function updateType($id, array $data)
    {
        $type = VolunteerType::findOrFail($id);
        $type->update($data);
        return $type;
    }

    public function deleteType($id)
    {
        $type = VolunteerType::findOrFail($id);
        $type->delete();
        return $type;
    }
    public function getTypeById($id)
{
    return VolunteerType::findOrFail($id);
}

public function getTypesWithVolunteersCount()
{
    try {
        
        // استعلام بديل يعمل في جميع الحالات
        $types =VolunteerType ::table('volunteer_types')
            ->leftJoin('volunteer_requests', 'volunteer_types.id', '=', 'volunteer_requests.volunteer_type_id')
            ->select(
                'volunteer_types.id',
                'volunteer_types.name_en',
                'volunteer_types.slug',
                'volunteer_types.is_active',
                VolunteerType::raw('COUNT(volunteer_requests.id) as volunteers_count')
            )
            ->groupBy('volunteer_types.id', 'volunteer_types.name_en', 'volunteer_types.slug', 'volunteer_types.is_active')
            ->get();

        return [
            'success' => true,
            'data' => $types,
            'message' => 'تم جلب البيانات بنجاح'
        ];

    } catch (\Exception $e) {
        \Log::error('Volunteer types count error: '.$e->getMessage());
        return [
            'success' => false,
            'message' => 'حدث خطأ تقني: '.$e->getMessage(),
            'data' => []
        ];
    }
}
public function getVolunteersByType($typeId)
{
    return VolunteerRequest::where('volunteer_type_id', $typeId)
                         ->with('user')
                         ->get();
}
}