@extends('layouts.master')
@section('title')
    {{ __('Notifications') }}
@endsection
@section('content')
    <x-page-title title="Notifications" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('notification-table')
        </div>
    </div>
@endsection
