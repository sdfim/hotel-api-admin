@extends('layouts.master')
@section('title')
    {{ __('General Configuration') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
          rel="stylesheet"
          type="text/css">

    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!-- -->
    <x-page-title title="General Configuration" pagetitle="index"/>
    <div class="card dark:bg-zinc-800 dark:border-zinc-600">
        <div class="card-body">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-6">
                    <div>
                        <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                            @livewire('general-configuration.create-general-configuration-form',
                            compact('general_configuration'))
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
