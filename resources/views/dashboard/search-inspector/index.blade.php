@extends('layouts.master')
@section('title')
    {{ __('Search Inspector') }}
@endsection
@section('css')
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
    <x-page-title title="Search Inspector" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('inspectors.search-inspector-table')
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

        function openModalError(id, type) {
            const el = document.querySelector('#modal-idmodal-' + id + '-' + type);
            el.classList.remove("hidden");
            el.classList.add("opened-modal-block");
        }

        function closeModalError(id, type) {
            const el = document.querySelector('#modal-idmodal-' + id + '-' + type);
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
