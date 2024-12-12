<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamController as TeamBaseController;

class TeamController extends TeamBaseController
{
    private array $message = ['edit' => 'Edit Team'];

    public function index(): View
    {
        Gate::authorize('view', Auth::user()->currentTeam);

        return view('dashboard.teams.index');
    }

    public function edit(string $id): View
    {
        $team = Team::findOrFail($id);

        Gate::authorize('update', $team);

        $text = $this->message;

        return view('dashboard.teams.form', compact('team', 'text'));
    }

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
