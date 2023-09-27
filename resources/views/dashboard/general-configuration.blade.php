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
                        <form class="mt-6" method="POST" action="{{Route('general_configuration_save')}}">
                            @csrf
                            <div class="grid grid-cols-12 mb-4">
                                <label for="time_supplier_requests-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">Time
                                    out on supplier requests</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input type="number"
                                           class="w-full py-1.5 placeholder:text-sm border-gray-100 rounded dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                           id="time_supplier_requests-input" placeholder=""
                                           name="time_supplier_requests"
                                           value="{{$general_configuration['time_supplier_requests'] ?? ''}}">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 mb-4">
                                <label for="time_reservations_kept-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">Length
                                    of Time Reservations are kept are offloading</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input type="number"
                                           class="w-full py-1.5 placeholder:text-sm border-gray-100 rounded dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                           id="time_reservations_kept-input" placeholder=""
                                           name="time_reservations_kept"
                                           value="{{$general_configuration->time_reservations_kept ?? ''}}">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 mb-4">
                                <label for="currently_suppliers-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">Which
                                    Suppliers are currently being searched for</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input type="text"
                                           class="w-full py-1.5 placeholder:text-sm border-gray-100 rounded dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                           id="currently_suppliers-input" placeholder="" name="currently_suppliers"
                                           value="{{$general_configuration->currently_suppliers ?? ''}}">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 mb-4">
                                <label for="time_inspector_retained-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">How
                                    Long Inspector Data is retained</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input type="number"
                                           class="w-full py-1.5 placeholder:text-sm border-gray-100 rounded dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                           id="time_inspector_retained-input" placeholder=""
                                           name="time_inspector_retained"
                                           value="{{$general_configuration->time_inspector_retained ?? ''}}">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 mb-4">
                                <label for="star_ratings-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">What
                                    star ratings to be searched for on the system</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input
                                        class="w-full rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                        type="datetime-local"
                                        value="{{$general_configuration->star_ratings ?? '2019-08-19T13:45:00'}}"
                                        id="star_ratings-input" name="star_ratings">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 mb-4">
                                <label for="stop_bookings-input"
                                       class="col-span-12 lg:col-span-3 font-medium text-gray-700 dark:text-zinc-100">Stop
                                    bookings with in a number of days / hours from time of search execution</label>
                                <div class="col-span-12 lg:col-span-9">
                                    <input
                                        class="w-full rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                        type="datetime-local"
                                        value="{{$general_configuration->stop_bookings ?? '2019-08-19T13:45:00'}}"
                                        id="stop_bookings-input" name="stop_bookings">
                                </div>
                            </div>
                            <div class="grid grid-cols-12 justify-content-end">
                                <div class="col-span-12 lg:col-span-9">
                                    <div class="mt-6">
                                        <button type="submit"
                                                class="btn bg-violet-500 border-transparent text-white font-medium w-28 hover:bg-violet-700 focus:bg-violet-700 focus:ring focus:ring-violet-50">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <!-- Plugins js-->
    <script
        src="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script
        src="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}">
    </script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard.init.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/nav&tabs.js') }}"></script>

    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/login.init.js') }}"></script>
@endsection
