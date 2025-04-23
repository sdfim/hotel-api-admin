<?php

namespace App\Http\Middleware;

use App\Models\TeamInvitation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set current team after accepting invitation
 */
class SelectTeamAfterAcceptMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $invitationId = explode('/', $request->path())[1];
        $invitation = TeamInvitation::findOrFail($invitationId);
        $teamId = $invitation->team_id;
        $app = $next($request);

        $user->refresh();
        if (! $user->current_team_id) {
            $user->update(['current_team_id' => $teamId]);
        }

        return $app;
    }
}
