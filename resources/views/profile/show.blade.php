@extends('layouts.master')
@section('title')
    Profile
@endsection
@section('content')
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border/>
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border/>
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border/>
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

                <div class="space-y-8">
                    <!-- Current Team Display -->
                    @if (Auth::user()->currentTeam)
                        <x-section-border/>

                        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Current
                                Team: {{ Auth::user()->currentTeam->name }}</h2>
                        </div>

                        <!-- Team Switcher -->
                        <x-section-border/>

                        <div class="mt-10">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Switch Teams</h3>
                            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                @foreach (Auth::user()->allTeams() as $team)
                                    <form action="{{ route('teams.switch') }}" method="POST"
                                          class="p-4 bg-white dark:bg-gray-900 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                        @csrf
                                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                                        <button type="submit" class="text-gray-600 dark:text-gray-400 font-medium">{{ $team->name }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Account Deletion Section -->
                    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                        <x-section-border/>

                        <div class="mt-10 sm:mt-0 bg-white dark:bg-gray-900 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Delete Account</h3>
                            @livewire('profile.delete-user-form')
                        </div>
                    @endif

                    <!-- Team Management Section -->
                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <x-section-border/>

                        <div class="mt-10 sm:mt-0 bg-white dark:bg-gray-900 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Team Members</h3>
                            @livewire('teams.team-member-manager', ['team' => Auth::user()->currentTeam])
                        </div>

                        <x-section-border/>

                        <div class="mt-10 sm:mt-0 bg-white dark:bg-gray-900 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Create New Team</h3>
                            @livewire('teams.create-team-form')
                        </div>
                    @endif
                </div>

        </div>
@endsection
