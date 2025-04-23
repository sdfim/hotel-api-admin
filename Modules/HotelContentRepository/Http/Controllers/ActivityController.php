<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all();

        return view('dashboard.activities.index', compact('activities'));
    }

    public function show($id)
    {
        $activity = Activity::findOrFail($id);

        return view('dashboard.activities.show', compact('activity'));
    }
}
