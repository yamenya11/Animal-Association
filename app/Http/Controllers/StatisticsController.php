<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Animal;
use App\Models\Appointment;
use App\Models\Ambulance;
use App\Models\AnimalCase;
use App\Models\AnimalType;
use Illuminate\Support\Facades\DB;
class StatisticsController extends Controller
{
     public function generalStats()
    {
        $stats = [
            'animals' => [
                'total' => Animal::count(),
                'adopted' => Animal::where('is_adopted', true)->count(),
                'available_for_adoption' => Animal::where('is_adopted', false)->count(),
                'available_for_care' => Animal::where('available_for_care', true)->count(),
              
            ],
            
            'appointments' => [
                'total' => Appointment::count(),
                'completed' => Appointment::where('status', 'completed')->count(),
                'immediate' => Appointment::where('is_immediate', true)->count(),
                'regular' => Appointment::where('is_immediate', false)->count()
            ],
            
            'ambulances' => [
                'total' => Ambulance::count(),
                'available' => Ambulance::where('status', 'available')->count(),
                'on_mission' => Ambulance::where('status', 'on_mission')->count(),
                'maintenance' => Ambulance::where('status', 'maintenance')->count()
            ],
            
            'animal_cases' => [
                'total' => AnimalCase::count(),
                'approved' => AnimalCase::where('approval_status', 'approved')->count(),
                'pending' => AnimalCase::where('approval_status', 'pending')->count(),
                'rejected' => AnimalCase::where('approval_status', 'rejected')->count(),
                'immediate' => AnimalCase::where('request_type', 'immediate')->count(),
                'regular' => AnimalCase::where('request_type', 'regular')->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'الإحصائيات العامة',
            'data' => $stats
        ]);
    }

       public function appointmentStats(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $query = Appointment::query();

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $stats = [
            'total_appointments' => $query->count(),
            'by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_type' => $query->select('is_immediate', DB::raw('count(*) as count'))
                ->groupBy('is_immediate')
                ->get(),
            'daily_count' => $query->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'message' => 'إحصائيات المواعيد',
            'data' => $stats
        ]);
    }
//احصائيات التبني والرعاية
  public function adoptionCareStats()
    {
        $stats = [
            'total_adoptions' => Animal::where('is_adopted', true)->count(),
            'pending_adoptions' => Animal::where('is_adopted', false)
                ->where('purpose', 'adoption')
                ->count(),
            'temporary_care' => Animal::where('available_for_care', true)->count(),
            'adoption_by_month' => Animal::where('is_adopted', true)
                ->select(
                    DB::raw('YEAR(adopted_at) as year'),
                    DB::raw('MONTH(adopted_at) as month'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'message' => 'إحصائيات التبني والرعاية',
            'data' => $stats
        ]);
    } // ← وهنا ينتهي التابع بشكل صحيح

    public function animalCaseStats()
    {
        $stats = [
            'total_cases' => AnimalCase::count(),
            'by_approval_status' => AnimalCase::select('approval_status', DB::raw('count(*) as count'))
                ->groupBy('approval_status')
                ->get(),
            'by_request_type' => AnimalCase::select('request_type', DB::raw('count(*) as count'))
                ->groupBy('request_type')
                ->get(),
            'cases_per_month' => AnimalCase::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'message' => 'إحصائيات الحالات الحيوانية',
            'data' => $stats
        ]);
    }

}
