<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamController as TeamBaseController;

class TeamController extends TeamBaseController
{
    public function switch(Request $request)
    {
        $team = Team::findOrFail($request->team_id);

        if (!Auth::user()->belongsToTeam($team)) {
            abort(403); // Если пользователь не состоит в этой команде
        }

        Auth::user()->switchTeam($team);

        return redirect()->back()->with('status', 'Команда успешно переключена');
    }
}
