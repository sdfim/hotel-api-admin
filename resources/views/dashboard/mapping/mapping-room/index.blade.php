@extends('layouts.master')
@section('title')
    {{ __('Mapping Rooms') }}
@endsection
@section('content')
    <x-page-title title="Mapping Rooms" pagetitle="index"/>
    <div class="card dark:bg-zinc-800 dark:border-zinc-600">
        @livewire('mapping.mapping-room-table')
    </div>
@endsection
