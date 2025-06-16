<?php

namespace App\Services;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminService
{
    // إدارة المستخدمين
    public function getAllUsers()
    {
        return User::select([
            'id',
            'name',
            'email',
            'created_at',
            'wallet_balance',
            'experience',
            'region'
        ])->get();
    }

    public function updateUser(Request $request, $userId): array
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'sometimes|min:6',
            'wallet_balance' => 'sometimes|numeric',
            'experience' => 'sometimes|numeric',
            'region' => 'sometimes|string'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return [
            'status' => true,
            'message' => 'تم تحديث بيانات المستخدم بنجاح',
            'data' => $user
        ];
    }

    public function deleteUser($userId): array
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return [
            'status' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ];
    }

    // إدارة الخدمات
    public function getAllServices()
    {
        return Service::all();
    }

    public function createService(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|integer'
        ]);

        $service = Service::create($validated);

        return [
            'status' => true,
            'message' => 'تم إضافة الخدمة بنجاح',
            'data' => $service
        ];
    }

    public function updateService(Request $request, $serviceId): array
    {
        $service = Service::findOrFail($serviceId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'duration' => 'sometimes|integer'
        ]);

        $service->update($validated);

        return [
            'status' => true,
            'message' => 'تم تحديث الخدمة بنجاح',
            'data' => $service
        ];
    }

    public function deleteService($serviceId): array
    {
        $service = Service::findOrFail($serviceId);
        $service->delete();

        return [
            'status' => true,
            'message' => 'تم حذف الخدمة بنجاح'
        ];
    }

    // التقارير
    public function generatePerformanceReport()
    {
        $report = [
            'total_users' => User::count(),
            'total_adoptions' => DB::table('adoptions')->count(),
            'total_appointments' => DB::table('appointments')->count(),
            'total_volunteers' => DB::table('volunteer_requests')
                ->where('status', 'approved')
                ->count(),
            'revenue' => DB::table('services')
                ->join('appointments', 'services.id', '=', 'appointments.service_id')
                ->where('appointments.status', 'completed')
                ->sum('services.price')
        ];

        return $report;
    }

    public function generateDailyReport()
    {
        $today = now()->format('Y-m-d');

        $report = [
            'new_users' => User::whereDate('created_at', $today)->count(),
            'new_adoptions' => DB::table('adoptions')
                ->whereDate('created_at', $today)
                ->count(),
            'new_appointments' => DB::table('appointments')
                ->whereDate('created_at', $today)
                ->count(),
            'completed_appointments' => DB::table('appointments')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'daily_revenue' => DB::table('services')
                ->join('appointments', 'services.id', '=', 'appointments.service_id')
                ->whereDate('appointments.created_at', $today)
                ->where('appointments.status', 'completed')
                ->sum('services.price')
        ];

        return $report;
    }
} 