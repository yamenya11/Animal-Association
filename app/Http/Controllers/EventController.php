<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EventService; 
use App\Models\Event; 
class EventController extends Controller
{
     protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

  // app/Http/Controllers/EventController.php
public function dashboard()
{
    $events = Event::orderBy('start_date')->get();
    
    return response()->json([
        'stats' => [
            'total_views' => $events->sum('views'), // مجموع المشاهدات
            'total_events' => $events->count()
        ],
        'events' => $events->map(function($event) {
            return [
                'title' => $event->title,
                'views' => $event->views, // عدد مشاهدات كل فعالية
                'time' => $event->start_date->format('H:i')
            ];
        })
    ]);
}

public function show($id)
{
    $event = Event::find($id);
    $event->increment('views'); // تزيد المشاهدات بمقدار 1
    
    return response()->json($event);
}
}
