@extends('layouts.master')
@section('title')
    {{ __('Activities') }}
@endsection
@section('content')
    <h2 class="font-semibold">Activities</h2>

    <div class="col-span-12">
        <div class="relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            @livewire('activity.activity-table')
        </div>
    </div>
@endsection