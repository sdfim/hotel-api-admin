@extends('layouts.master')
@section('title')
    {{ __('Clear Response') }}
@endsection
@section('content')
    <style>
        json-viewer {
            padding: 10px;
            margin-top: 10px;
            word-wrap: break-word;
        }
        .active-tab {
            border: 2px solid #FF7C1A;
            font-weight: bold;
        }
    </style>

    <div class="col-span-12 xl:col-span-6">
        <div class="card-body pb-0 flex">
            <x-button-back route="{{ redirect()->getUrlGenerator()->previous() }}" text="Back"/>
            <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 ml-4">Booking Inspector</h6>
        </div>
        <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            <div class="relative overflow-x-auto">
                <div class="row">
                    <div class="col-lg-12 margin-tb">
                        <div class="mt-2">
                            <strong>Booking ID:</strong>
                            {{ $inspector->booking_id }}
                        </div>
                        <div class="mt-2">
                            <strong>Search ID:</strong>
                            {{ $inspector->search_id }}
                        </div>

                        <div class="mt-2">
                            <strong>Booking Item:</strong>
                            {{ $inspector->booking_item }}
                        </div>
                        <div class="mt-2">
                            <strong>Search Type:</strong>
                            {{ $inspector->search_type }}
                        </div>
                        <div class="mt-2">
                            <strong>Endpoint:</strong>
                            {{ $inspector->type }}
                            <strong>step:</strong>
                            {{ $inspector->sub_type }}
                        </div>
                        <div class="mt-2">
                            <strong>Supplier:</strong>
                            {{ $inspector->Supplier->name }}
                        </div>
                        <div class="mt-2">
                            <strong>Channel:</strong>
                            {{ $inspector->token->name }}
                        </div>
                        <div class="card mt-6 p-5">
                            <button type="button"
                                    class="text-white px-2 py-2 bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600"
                                    data-tw-toggle="modal" data-tw-target="#modal-idmediummodal">View Request
                            </button>

                            @if($inspector->sub_type === 'create')
                                <button type="button"
                                        class="text-white px-2 py-2 bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600"
                                        data-tw-toggle="modal" data-tw-target="#modal-file-response">View Response
                                </button>
                            @endif

                            <button type="button"
                                    class="text-white px-2 py-2 bg-gray-500 border-blue-500 btn hover:bg-gray-600 focus:ring ring-gray-200 focus:bg-gray-600"
                                    id="loadResponse"><i class="fas fa-download"></i> Response as JSON
                            </button>

                            @if ($inspector->sub_type == 'create' || $inspector->sub_type == 'retrieve' || $inspector->sub_type == 'change-soft')
                                <button type="button"
                                        class="text-white px-2 py-2 bg-gray-500 border-blue-500 btn hover:bg-gray-600 focus:ring ring-gray-200 focus:bg-gray-600"
                                        id="downLoadRawRequest"><i class="fas fa-download"></i> Raw Request
                                </button>

                                <button type="button"
                                        class="text-white px-2 py-2 bg-gray-500 border-blue-500 btn hover:bg-gray-600 focus:ring ring-gray-200 focus:bg-gray-600"
                                        id="downloadRawResponse"><i class="fas fa-download"></i> Raw Response
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-10 sm:mt-0">

                </div>
            </div>

            <div class="card mt-12 p-5">
                <div class="flex flex-wrap gap-2 mb-4">
                    <button data-tw-toggle="tab" data-tw-target="#tab-pills-origin"
                            class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-orange-100 text-orange-600 active-tab">
                        Original Request and Response
                    </button>
                    <button data-tw-toggle="tab" data-tw-target="#tab-pills-response"
                            class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-600">
                        Supplier Response
                    </button>
                    @if($inspector->client_response_path)
                        <button data-tw-toggle="tab" data-tw-target="#tab-pills-client-response"
                                class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-600">
                            <?= env('APP_NAME'); ?> API RS
                        </button>
                    @endif
                </div>
                <div class="relative z-50 hidden modal" id="modal-idmediummodal" aria-labelledby="modal-title"
                     role="dialog" aria-modal="true">
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="absolute inset-0 transition-opacity bg-black bg-opacity-50 modal-overlay"></div>
                        <div class="p-4 mx-auto animate-translate max-w-3xl">
                            <div
                                class="relative overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-600">
                                <div class="bg-white dark:bg-zinc-700">
                                    <div
                                        class="flex items-center p-4 border-b rounded-t border-gray-50 dark:border-zinc-600">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 ">
                                            Response {{$inspector->id}}
                                        </h3>
                                        <button
                                            class="inline-flex items-center px-2 py-1 text-sm text-gray-400 border-transparent rounded-lg btn hover:bg-gray-50/50 hover:text-gray-900 dark:text-gray-100 ltr:ml-auto rtl:mr-auto dark:hover:bg-zinc-600"
                                            type="button" data-tw-dismiss="modal">
                                            <i class="text-xl text-gray-500 mdi mdi-close dark:text-zinc-100/60"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-6 ltr:text-left rtl:text-right">
                                            <pre
                                                style="font-size: 0.8em;">{{json_encode(json_decode($inspector->request), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    <!-- Modal footer -->
                                    <div
                                        class="flex items-center gap-3 p-5 space-x-2 border-t rounded-b border-gray-50 dark:border-zinc-600">

                                        <button type="button"
                                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm btn dark:text-gray-100 hover:bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-gray-500/30 sm:mt-0 sm:w-auto sm:text-sm dark:bg-zinc-700 dark:border-zinc-600 dark:hover:bg-zinc-600 dark:focus:bg-zinc-600 dark:focus:ring-zinc-700 dark:focus:ring-gray-500/20"
                                                data-tw-dismiss="modal">Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 tab-content">
                    <div class="block tab-pane" id="tab-pills-origin">
                        <p class="mb-0 dark:text-gray-300">
                        @php
                            $path = str_replace('json', 'original.json', $inspector->response_path);
                            $file_original = Storage::get($path);
                            if($file_original == ''){
                                $file_original = json_encode([]);
                            }
                        @endphp
                        <div id="actions-toolbar">
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="expand-original">Expand All
                            </button>
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="collapse-original">Collapse All
                            </button>
                            <input
                                class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-mandarin-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                id="search-original" placeholder="search"></input>
                        </div>
                        <json-viewer id="json-original" style="font-size:0.8em"></json-viewer>
                    </div>
                    <div class="hidden tab-pane" id="tab-pills-response">
                        <p class="mb-0 dark:text-gray-300">
                        @php
                            $file_response = Storage::get($inspector->response_path);
                            if($file_response == ''){
                                $file_response = json_encode([]);
                            }
                        @endphp
                        <div id="actions-toolbar">
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="expand-response">Expand All
                            </button>
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="collapse-response">Collapse All
                            </button>
                            <input
                                class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-mandarin-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                id="search-response" placeholder="search"></input>
                        </div>
                        <json-viewer id="json-response" style="font-size:0.8em"></json-viewer>
                    </div>
                    <div class="hidden tab-pane" id="tab-pills-client-response">
                        <p class="mb-0 dark:text-gray-300">
                        @php
                            $file_client_response = Storage::get($inspector->client_response_path);
                            if($file_client_response == ''){
                                $file_client_response = json_encode([]);
                            }
                        @endphp
                        <div id="actions-toolbar">
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="expand-client">Expand All
                            </button>
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="collapse-client">Collapse All
                            </button>
                            <input
                                class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-mandarin-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                id="search-client" placeholder="search"></input>
                        </div>
                        <json-viewer id="json-client" style="font-size:0.8em"></json-viewer>
                    </div>
                </div>

                <div class="relative z-50 hidden modal" id="modal-file-response" aria-labelledby="modal-title"
                     role="dialog" aria-modal="true">
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="absolute inset-0 transition-opacity bg-black bg-opacity-50 modal-overlay"></div>
                        <div class="p-4 mx-auto animate-translate max-w-3xl">
                            <div
                                class="relative overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-600">
                                <div class="bg-white dark:bg-zinc-700">
                                    <div
                                        class="flex items-center p-4 border-b rounded-t border-gray-50 dark:border-zinc-600">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 ">
                                            Response {{$inspector->id}}
                                        </h3>
                                        <button
                                            class="inline-flex items-center px-2 py-1 text-sm text-gray-400 border-transparent rounded-lg btn hover:bg-gray-50/50 hover:text-gray-900 dark:text-gray-100 ltr:ml-auto rtl:mr-auto dark:hover:bg-zinc-600"
                                            type="button" data-tw-dismiss="modal">
                                            <i class="text-xl text-gray-500 mdi mdi-close dark:text-zinc-100/60"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-6 ltr:text-left rtl:text-right">
                                            <pre
                                                style="font-size: 0.8em;">{{json_encode(json_decode($file_response), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    <!-- Modal footer -->
                                    <div
                                        class="flex items-center gap-3 p-5 space-x-2 border-t rounded-b border-gray-50 dark:border-zinc-600">

                                        <button type="button"
                                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm btn dark:text-gray-100 hover:bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-gray-500/30 sm:mt-0 sm:w-auto sm:text-sm dark:bg-zinc-700 dark:border-zinc-600 dark:hover:bg-zinc-600 dark:focus:bg-zinc-600 dark:focus:ring-zinc-700 dark:focus:ring-gray-500/20"
                                                data-tw-dismiss="modal">Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/js/json-viewer.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/nav&tabs.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('[data-tw-toggle="tab"]');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    tabs.forEach(t => t.classList.remove('active-tab'));
                    tabPanes.forEach(pane => pane.classList.add('hidden'));
                    this.classList.add('active-tab');
                    const target = document.querySelector(this.getAttribute('data-tw-target'));
                    if (target) {
                        target.classList.remove('hidden');
                    }
                });
            });
        });

        var fileOriginal = {!! $file_original !!};
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];

        //original request request
        document.querySelector('#json-original').data = <?= $file_original ?>;
        const original_viewer = document.querySelector('#json-original');
        const original_expand = document.querySelector('#expand-original');
        const original_collapse = document.querySelector('#collapse-original');
        const original_search = document.querySelector('#search-original');
        let currentSearch_original;
        original_expand.addEventListener('click', (e) => {
            e.preventDefault();
            original_viewer.expandAll();
        });

        original_collapse.addEventListener('click', (e) => {
            e.preventDefault();
            original_viewer.collapseAll();
        });
        original_search.addEventListener('input', () => {
            currentSearch_response = original_viewer.search(original_search.value);
        });
        original_search.addEventListener('keyup', (e) => {
            if (currentSearch_original && e.keyCode === 13) {
                currentSearch_original.next();
            }
        });

        // response
        document.querySelector('#json-response').data = <?= $file_response ?>;
        const viewer = document.querySelector('#json-response');
        const expand = document.querySelector('#expand-response');
        const collapse = document.querySelector('#collapse-response');
        const search = document.querySelector('#search-response');
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

        //client response
        document.querySelector('#json-client').data = <?= $file_client_response ?>;
        const viewerClient = document.querySelector('#json-client');
        const expandClient = document.querySelector('#expand-client');
        const collapseClient = document.querySelector('#collapse-client');
        const searchClient = document.querySelector('#search-client');
        let currentSearch_client;
        expandClient.addEventListener('click', (e) => {
            e.preventDefault();
            viewerClient.expandAll();
        });

        collapseClient.addEventListener('click', (e) => {
            e.preventDefault();
            viewerClient.collapseAll();
        });
        searchClient.addEventListener('input', () => {
            currentSearch_client = viewerClient.search(searchClient.value);
        });
        searchClient.addEventListener('keyup', (e) => {
            if (currentSearch_client && e.keyCode === 13) {
                currentSearch_client.next();
            }
        });

        document.getElementById('loadResponse').addEventListener('click', function () {
            var blob = new Blob([<?= json_encode($file_client_response) ?>], {type: "application/json;charset=utf-8"});
            saveAs(blob, "file.json");
        });


        document.getElementById('downLoadRawRequest').addEventListener('click', function () {
            if (fileOriginal.request) {
                if ('{{ $inspector->Supplier->name }}' === 'HBSI') {
                    var blob = new Blob([fileOriginal.request], {type: "application/plain;charset=utf-8"});
                    saveAs(blob, `request_{{$inspector->Supplier->name}}_${formattedDate}_{{$inspector->booking_item}}.txt`);
                }
                if ('{{ $inspector->Supplier->name }}' === 'Expedia') {
                    var jsonString = JSON.stringify(fileOriginal.request);
                    var blob = new Blob([jsonString], {type: "application/json;charset=utf-8"});
                    saveAs(blob, `request_{{$inspector->Supplier->name}}_${formattedDate}_{{$inspector->booking_item}}.json`);
                }
            }
        });

        document.getElementById('downloadRawResponse').addEventListener('click', function () {
            if (fileOriginal.response) {
                if ('{{ $inspector->Supplier->name }}' === 'HBSI') {
                    var blob = new Blob([fileOriginal.response], {type: "application/plain;charset=utf-8"});
                    saveAs(blob, `response_{{$inspector->supplier->name}}_${formattedDate}_{{$inspector->booking_item}}.txt`);
                }
                if ('{{ $inspector->Supplier->name }}' === 'Expedia') {
                    var jsonString = JSON.stringify(fileOriginal.response);
                    var blob = new Blob([jsonString], {type: "application/json;charset=utf-8"});
                    saveAs(blob, `response_{{$inspector->supplier->name}}_${formattedDate}_{{$inspector->booking_item}}.json`);
                }
            }
        });

    </script>
@endsection
