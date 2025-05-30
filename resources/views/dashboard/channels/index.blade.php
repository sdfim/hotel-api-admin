@extends('layouts.master')
@section('title')
    {{ __('General Channels') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="General channels" pagetitle="index"/>
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="mb-6">
                <a class="btn text-mandarin-500 hover:text-white border-mandarin-500 hover:bg-mandarin-600 hover:border-mandarin-600 focus:bg-mandarin-600 focus:text-white focus:border-mandarin-600 focus:ring focus:ring-mandarin-500/30 active:bg-mandarin-600 active:border-mandarin-600"
                   href="{{ route('channels.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('channels.channels-table')
        </div>
    </div>
@endsection
