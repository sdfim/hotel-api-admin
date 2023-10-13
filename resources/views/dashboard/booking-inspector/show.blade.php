@extends('layouts.master')
@section('title')
    {{ __('Response clear') }}
@endsection
@section('content')
    <style>
        json-viewer {
            padding: 10px;
            margin-top: 10px;
            word-wrap: break-word;
        }
    </style>
    <script src="https://unpkg.com/@alenaksu/json-viewer@2.0.0/dist/json-viewer.bundle.js"></script>

    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0 flex">
                <x-button-back route="{{ redirect()->getUrlGenerator()->previous() }}" text="Back"/>
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 ml-4">Booking Inspector</h6>
            </div>
            <div class="card-body">
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
                        </div>
                    </div>
                    <div class="mt-10 sm:mt-0">

                    </div>
                    <x-section-border/>
                </div>
                <div class="nav-tabs tab-pill">
                    <ul class="flex flex-wrap w-full text-sm font-medium text-center text-gray-500 nav">
                        <li>
                            <a href="javascript:void(0);" data-tw-toggle="tab" data-tw-target="tab-pills-response"
                               class="inline-block px-4 py-3 rounded-md active">Supplier Response</a>
                        </li>
                        @if($inspector->client_response_path)
                            <li>
                                <a href="javascript:void(0);" data-tw-toggle="tab"
                                   data-tw-target="tab-pills-client-response"
                                   class="inline-block px-4 py-3 rounded-md dark:hover:text-white">UJV API Response</a>
                            </li>
                        @endif
                        <li>
                            <button type="button"
                                    class="text-white px-4 py-3 bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600 ml-2"
                                    data-tw-toggle="modal" data-tw-target="#modal-idmediummodal">Request
                            </button>
                        </li>
                    </ul>
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
                        <div class="block tab-pane" id="tab-pills-response">
                            <p class="mb-0 dark:text-gray-300">
                                @php
                                    $file_response = Storage::get($inspector->response_path);
                                    if($file_response == ''){
                                        $file_response = json_encode([]);
                                    }
                                @endphp
                                <strong>Response:</strong>
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
                                    class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                    id="search-response" placeholder="search"></input>
                            </div>
                            <json-viewer id="json-response" style="font-size:0.8em"></json-viewer>
                            </p>
                        </div>
                        <div class="hidden tab-pane" id="tab-pills-client-response">
                            <p class="mb-0 dark:text-gray-300">
                                @php
                                    $file_client_response = Storage::get($inspector->client_response_path);
                                    if($file_client_response == ''){
                                        $file_client_response = json_encode([]);
                                    }
                                @endphp
                                <strong>Client response:</strong>
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
                                    class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                    id="search-client" placeholder="search"></input>
                            </div>
                            <json-viewer id="json-client" style="font-size:0.8em"></json-viewer>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/js/pages/nav&tabs.js') }}"></script>

    <script type="module">
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

    </script>
@endsection
