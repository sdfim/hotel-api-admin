@extends('layouts.master')
@section('title')
    {{ __('Channels') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Channels" pagetitle="index" />
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('channels.channels-table')
        </div>
    </div>
@endsection
