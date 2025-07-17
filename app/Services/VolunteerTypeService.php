<?php

namespace App\Services;

use App\Models\VolunteerType;

class VolunteerTypeService
{
    public function getAllTypes()
    {
        return VolunteerType::where('is_active', true)->get();
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
    return VolunteerType::withCount('volunteerRequests')
        ->get()
        ->map(function ($type) {
            return [
                'id' => $type->id,
                
                'name_en' => $type->name_en,
                'slug' => $type->slug,
                'volunteers_count' => $type->volunteer_requests_count,
                'is_active' => $type->is_active
            ];
        });
}
public function getVolunteersByType($typeId)
{
    return VolunteerRequest::where('volunteer_type_id', $typeId)
                         ->with('user')
                         ->get();
}
}