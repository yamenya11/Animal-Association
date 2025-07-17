<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventService
{
   public function getDashboardData()
{
    return [
        'counts' => [
            'new_announcements' => Event::announcements()
                ->where('created_at', '>', now()->subDays(3))
                ->count(),
            'upcoming_events' => Event::events()
                ->upcoming()
                ->count(),
            'total_views' => Event::sum('views')
        ],
        'recent_announcements' => Event::announcements()
            ->latest()
            ->take(3)
            ->get(['id', 'title', 'description', 'created_at', 'status']),
        'upcoming_events' => Event::events()
            ->upcoming()
            ->orderBy('start_date')
            ->take(2)
            ->get(['id', 'title', 'start_date', 'location']),
        'trainings' => Event::trainings()
            ->upcoming()
            ->orderBy('start_date')
            ->take(1)
            ->get(['id', 'title', 'start_date', 'location'])
    ];
}
}