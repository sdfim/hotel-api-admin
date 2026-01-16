@extends('layouts.master')
@section('title')
    {{ __('View Activity Log') }}
@endsection
@section('content')
    <h2 class="font-semibold">View Activity Log {{$activity->id}}</h2>
    <div class="col-span-12">
        <div class="relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            <p><strong>ID:</strong> {{ $activity->id }}</p>
            <p><strong>Log Name:</strong> {{ $activity->log_name }}</p>
            <p><strong>Description:</strong> {{ $activity->description }}</p>
            <p><strong>Subject Type:</strong> {{ $activity->subject_type }}</p>
            <p><strong>Event:</strong> {{ $activity->event }}</p>
            <p><strong>Subject ID:</strong> {{ $activity->subject_id }}</p>
            <p><strong>Causer Type:</strong> {{ $activity->causer_type }}</p>
            <p><strong>Causer</strong></p>
            <p class="pl-6"><strong>Causer ID:</strong> {{ $activity->causer_id }}</p>
            <p class="pl-6"><strong>Name:</strong> {{ $activity->causer?->name }}</p>
            <p class="pl-6"><strong>Email:</strong> {{ $activity->causer?->email }}</p>
            <p class="pl-6"><strong>Roles:</strong></p>
            <ul>
                @foreach($activity->causer?->roles ?? [] as $role)
                    <li class="pl-12">{{ $role->name }}</li>
                @endforeach
            </ul>
            <p class="pl-6"><strong>Teams:</strong></p>
            <ul>
                @foreach($activity->causer?->teams ?? [] as $team)
                    <li class="pl-12">{{ $team->name }}</li>
                @endforeach
            </ul>
            <p><strong>Properties:</strong></p>
            <pre>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
            <p><strong>Batch UUID:</strong> {{ $activity->batch_uuid }}</p>
            <p><strong>Created At:</strong> {{ $activity->created_at }}</p>
        </div>
    </div>
@endsection