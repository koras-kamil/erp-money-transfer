<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Get last 50 activities with the User (causer) info
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(20);

        return view('activity-log.index', compact('activities'));
    }
}