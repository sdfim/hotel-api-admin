@extends('layouts.master')
@section('title')
    {{ __('View Activity Log') }}
@endsection
@section('content')
    <div class="breadcrumb-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('activities.index') }}">Activities</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Activity Log {{$activity->id}}</li>
            </ol>
        </nav>
    </div>
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
            <p><strong>Causer ID:</strong> {{ $activity->causer_id }}</p>
            <p><strong>Properties:</strong></p>
            <pre>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
            <p><strong>Batch UUID:</strong> {{ $activity->batch_uuid }}</p>
            <p><strong>Created At:</strong> {{ $activity->created_at }}</p>
        </div>
    </div>
@endsection
