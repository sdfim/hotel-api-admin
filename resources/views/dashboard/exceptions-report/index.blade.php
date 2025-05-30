@extends('layouts.master')
@section('title')
    {{ __('Exceptions Report') }}
@endsection
@section('css')
    <style>
        .modal-body {
            padding: 20px;
            max-height: 450px;
            overflow-x: auto;
        }
    </style>
@endsection
@section('content')
    <x-page-title title="Exceptions Report" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('api-exception-reports-table')
        </div>
    </div>
    </div>
@endsection
@section('js')
    <script src="{{ URL::asset('build/js/json-viewer.js') }}"></script>
    <script>
        function openModal(id, type, json) {
            const el = document.querySelector('#modal-idlargemodal-' + id + '-' + type);
            el.classList.remove("hidden");
            el.classList.add("opened-modal-block");
            insertJSON(id, json);
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

        function insertJSON(id, json) {
            document.querySelector('#json-response-' + id).data = json;
            const viewer = document.querySelector('#json-response-' + id);
            const expand = document.querySelector('#expand-response-' + id);
            const collapse = document.querySelector('#collapse-response-' + id);
            const search = document.querySelector('#search-response-' + id);
            let currentSearch_response;
            expand.addEventListener('click', (e) => {
                e.preventDefault();
                viewer.expandAll();
            });

            collapse.addEventListener('click', (e) => {
                e.preventDefault();
                viewer.collapseAll();
            });
            search.addEventListener('input', () => {
                currentSearch_response = viewer.search(search.value);
            });
            search.addEventListener('keyup', (e) => {
                if (currentSearch_response && e.keyCode === 13) {
                    currentSearch_response.next();
                }
            });
        }
    </script>
@endsection
