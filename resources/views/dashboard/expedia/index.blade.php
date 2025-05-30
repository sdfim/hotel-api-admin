@extends('layouts.master')
@section('title')
    {{ __('Expedia') }}
@endsection
@section('content')
    <style>
        .bg-black {
            --tw-bg-opacity: 0.5 !important;
            background-color: rgb(0 0 0 / var(--tw-bg-opacity)) !important;
        }

        .modal-body {
            position: relative;
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1rem;
            text-wrap: wrap;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.47rem 0.75rem;
            font-size: .875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #313533;
            background-color: #f8f9fa;
            background-clip: padding-box;
            border: 1px solid #e9e9ef;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 0.25rem;
            -webkit-transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
        }
    </style>
    <!-- -->
    <x-page-title title="Expedia" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('expedia-table')
        </div>
    </div>
@endsection
@section('js')
    <script>
        function openModal(id) {
            const el = document.querySelector('#modal-idlargemodal-' + id);
            el.classList.remove("hidden");
            el.classList.add("opened-modal-block");
        }

        function closeModal(id) {
            const el = document.querySelector('#modal-idlargemodal-' + id);
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
