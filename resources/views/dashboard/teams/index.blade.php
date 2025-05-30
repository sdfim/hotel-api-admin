@extends('layouts.master')
@section('title')
    {{ __('Teams') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Teams" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('teams.teams-table')
        </div>
    </div>
@endsection
