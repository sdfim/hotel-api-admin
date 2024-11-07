@extends('layouts.master')
@section('title')
    Team Settings
@endsection
@section('content')
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Auth::user()->currentTeam)
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-700">Current
                        Team: {{ Auth::user()->currentTeam->name }}</h2>
                </div>

                <x-section-border/>

                <div class="mt-10">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Switch Teams</h3>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @foreach (Auth::user()->allTeams() as $team)
                            <form action="{{ route('teams.switch') }}" method="POST"
                                  class="p-4 bg-white rounded-lg shadow hover:bg-gray-50 transition duration-150">
                                @csrf
                                <input type="hidden" name="team_id" value="{{ $team->id }}">
                                <button type="submit" class="text-gray-600 font-medium">{{ $team->name }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <x-section-border/>
            @endif

            @livewire('teams.update-team-name-form', ['team' => $team])

            <x-section-border/>

            @livewire('teams.team-member-manager', ['team' => $team])

            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-section-border/>

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif
        </div>
    </div>
@endsection
