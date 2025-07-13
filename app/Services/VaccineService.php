<?php
// app/Services/VaccineService.php
namespace App\Services;

use App\Models\Vaccine;
use Illuminate\Http\Request;

class VaccineService
{
    public function create(Request $request): Vaccine
    {
        return Vaccine::create($request->only(['animal_name', 'type', 'due_date']));
    }

    public function list()
    {
        return Vaccine::orderBy('due_date', 'asc')->get();
    }

    public function dueToday()
    {
        return Vaccine::whereDate('due_date', now()->toDateString())->get();
    }
}
