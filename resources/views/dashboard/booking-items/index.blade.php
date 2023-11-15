@extends('layouts.master')
@section('title')
    {{ __('Booking Items') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
          rel="stylesheet"
          type="text/css">

    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <style>
        .modal-body {
            padding: 20px;
            max-height: 450px;
            overflow-x: auto;
        }
    </style>

@endsection
@section('content')

    <!-- -->
    <x-page-title title="Booking Items" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    @livewire('inspectors.booking-items-table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        function openModal(id, type) {
            const el = document.querySelector('#modal-idlargemodal-' + id + '-' + type);
            el.classList.remove("hidden");
            el.classList.add("opened-modal-block");
        }

        function closeModal(id, type) {
            const el = document.querySelector('#modal-idlargemodal-' + id + '-' + type);
            el.classList.add("hidden");
            el.classList.remove("opened-modal-block");
        }

        document.addEventListener('click', (e) => {
            const div = document.querySelector('.opened-modal-block');
            if (e.srcElement.classList.contains('modal-overlay') && div) {
                div.classList.add("hidden");
                div.classList.remove("opened-modal-block");
            }
        })


    </script>
@endsection
