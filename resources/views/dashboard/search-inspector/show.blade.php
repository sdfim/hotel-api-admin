@extends('layouts.master')
@section('title')
    {{ __('Clear response') }}
@endsection
@section('content')
    <style>
        json-viewer {
            padding: 10px;
            margin-top: 10px;
            font-size: 0.8em;
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
            <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 ml-4">Search Inspector</h6>
        </div>
        <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            <div class="relative overflow-x-auto">

                <div class="col-lg-12 margin-tb">
                    <div class="mt-2">
                        <strong>Search ID:</strong>
                        {{ $inspector->search_id }}
                    </div>
                    <div class="mt-2">
                        <strong>Search Type:</strong>
                        {{ $inspector->type }}
                    </div>
                    <div class="mt-2">
                        <strong>Supplier:</strong>
                        @php
                            $suppliers_name_string = \App\Models\Supplier::whereIn('id', explode(',', $inspector->suppliers))->pluck('name')->implode(', ');
                        @endphp
                        {{ $suppliers_name_string }}
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

                        <button type="button"
                                class="text-white px-2 py-2 bg-mandarin-500  btn hover:bg-sky-600 focus:ring ring-sky-200 focus:bg-sky-600"
                                id="loadRQ"><i class="fas fa-download"></i> Original RQ and RS as JSON
                        </button>
                        <button type="button"
                                class="text-white px-2 py-2 bg-mandarin-500  btn hover:bg-sky-600 focus:ring ring-sky-200 focus:bg-sky-600"
                                id="loadResponsePr"><i class="fas fa-download"></i> RS as JSON with <b>PricingRules</b> Applier
                        </button>
                        <button type="button"
                                class="text-white px-2 py-2 bg-mandarin-500  btn hover:bg-sky-600 focus:ring ring-sky-200 focus:bg-sky-600"
                                id="loadResponse"><i class="fas fa-download"></i> RS as JSON
                        </button>

                        <button type="button"
                                class="text-white px-2 py-2 bg-gray-500  btn hover:bg-gray-600 focus:ring ring-gray-200 focus:bg-gray-600"
                                id="downLoadRawRequest"><i class="fas fa-download"></i> Raw Request
                        </button>

                        <button type="button"
                                class="text-white px-2 py-2 bg-gray-500  btn hover:bg-gray-600 focus:ring ring-gray-200 focus:bg-gray-600"
                                id="downloadRawResponse"><i class="fas fa-download"></i> Raw Response
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-8 p-5">
                <!-- Табы в стиле филамента -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button data-tw-toggle="tab" data-tw-target="#tab-pills-origin"
                            class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-orange-100 text-orange-600 active-tab">
                        Original RQ and RS
                    </button>
                    @if($inspector->client_response_path)
                        <button data-tw-toggle="tab" data-tw-target="#tab-pills-client-response-pr"
                                class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-600">
                            TerraMare API RS WITH PricingRules Applier
                        </button>
                        <button data-tw-toggle="tab" data-tw-target="#tab-pills-client-response"
                                class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-600">
                            TerraMare API RS
                        </button>
                    @endif
                </div>

                <!-- Содержимое табов -->
                <div class="tab-content">
                    <div class="tab-pane block" id="tab-pills-origin">
                        <p class="mb-0 dark:text-gray-300 text-sm">
                        {{-- Original Request and Response --}}
                        @php
                            $path = str_replace('json', 'original.json', $inspector->response_path);
                            $file_original = Storage::get($path);
                            if ($file_original == '') {
                                $file_original = json_encode([]);
                            }
                            $file_size_bytes = Storage::size($path);

                            echo "File name: " . basename($path) . "<br>";
                            echo "File size: " . round(strlen($file_original) / (1024 * 1024), 2) . " MB<br>";

                            $count_request = 0;
                            $data = json_decode($file_original, true);
                            if (is_array($data)) {
                                foreach ($data as $suppliers_package) {
                                    if (isset($suppliers_package['request'])) {
                                        $count_request += is_array($suppliers_package['request'])
                                            ? count($suppliers_package['request'])
                                            : 1;
                                    }
                                }
                            }
                        @endphp
                        <div id="actions-toolbar">
                            <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                    id="expand-original">Expand All</button>
                            <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                    id="collapse-original">Collapse All</button>
                            <input class="rounded border-gray-100 py-2.5 text-sm text-gray-500"
                                   id="search-original" placeholder="search">
                        </div>
                        <json-viewer id="json-original" style="font-size:0.8em"></json-viewer>
                        </p>
                    </div>

                    <div class="tab-pane hidden" id="tab-pills-response">
                        <p class="mb-0 dark:text-gray-300 text-sm">
                        @php
                            $file_response = Storage::get($inspector->response_path);
                            if ($file_response == '') {
                                $file_response = json_encode([]);
                            }
                            $file_size_bytes = Storage::size($inspector->response_path);
                            echo "File name: " . basename($inspector->response_path) . "<br>";
                            echo "File size: " . round($file_size_bytes / (1024 * 1024), 2) . " MB<br>";
                        @endphp
                        <div id="actions-toolbar">
                            <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                    id="expand-response">Expand All</button>
                            <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                    id="collapse-response">Collapse All</button>
                            <input class="rounded border-gray-100 py-2.5 text-sm text-gray-500"
                                   id="search-response" placeholder="search">
                        </div>
                        <json-viewer id="json-response" style="font-size:0.8em"></json-viewer>
                        </p>
                    </div>

                    @if($inspector->client_response_path)
                        <div class="tab-pane hidden" id="tab-pills-client-response-pr">
                            <p class="mb-0 dark:text-gray-300 text-sm">
                            @php
                                $path = str_replace('json', 'client_with_pracing_rule_applier.json', $inspector->response_path);
                                $file_client_response_pr = Storage::get($path);
                                if ($file_client_response_pr == '') {
                                    $file_client_response_pr = json_encode([]);
                                }
                                $file_size_bytes = Storage::size($path);
                                echo "File name: " . basename($path) . "<br>";
                                echo "File size: " . round($file_size_bytes / (1024 * 1024), 2) . " MB";
                            @endphp
                            <div id="actions-toolbar">
                                <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                        id="expand-client-pr">Expand All</button>
                                <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                        id="collapse-client-pr">Collapse All</button>
                                <input class="rounded border-gray-100 py-2.5 text-sm text-gray-500"
                                       id="search-client-pr" placeholder="search">
                            </div>
                            <json-viewer id="json-client-pr" style="font-size:0.8em"></json-viewer>
                            </p>
                        </div>

                        <div class="tab-pane hidden" id="tab-pills-client-response">
                            <p class="mb-0 dark:text-gray-300 text-sm">
                            @php
                                $file_client_response = Storage::get($inspector->client_response_path);
                                if ($file_client_response == '') {
                                    $file_client_response = json_encode([]);
                                }
                                $file_size_bytes = Storage::size($inspector->client_response_path);
                                echo "File name: " . basename($inspector->client_response_path) . "<br>";
                                echo "File size: " . round($file_size_bytes / (1024 * 1024), 2) . " MB";
                            @endphp
                            <div id="actions-toolbar">
                                <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                        id="expand-client">Expand All</button>
                                <button class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600"
                                        id="collapse-client">Collapse All</button>
                                <input class="rounded border-gray-100 py-2.5 text-sm text-gray-500"
                                       id="search-client" placeholder="search">
                            </div>
                            <json-viewer id="json-client" style="font-size:0.8em"></json-viewer>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="relative z-50 hidden modal" id="modal-idmediummodal" aria-labelledby="modal-title"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="absolute inset-0 transition-opacity bg-black bg-opacity-50 modal-overlay"></div>
            <div class="p-4 mx-auto animate-translate sm:max-w-xl">
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
                            <pre>{{json_encode(json_decode($inspector->request), JSON_PRETTY_PRINT) }}</pre>
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

        //client response with pricing rules
        document.querySelector('#json-client-pr').data = <?= $file_client_response_pr ?>;
        const viewerClientPr = document.querySelector('#json-client-pr');
        const expandClientPr = document.querySelector('#expand-client-pr');
        const collapseClientPr = document.querySelector('#collapse-client-pr');
        const searchClientPr = document.querySelector('#search-client-pr');
        let currentSearchPr_client;
        expandClientPr.addEventListener('click', (e) => {
            e.preventDefault();
            viewerClientPr.expandAll();
        });

        collapseClientPr.addEventListener('click', (e) => {
            e.preventDefault();
            viewerClientPr.collapseAll();
        });
        searchClientPr.addEventListener('input', () => {
            currentSearchPr_client = viewerClientPr.search(searchClient.value);
        });
        searchClientPr.addEventListener('keyup', (e) => {
            if (currentSearchPr_client && e.keyCode === 13) {
                currentSearchPr_client.next();
            }
        });

        document.getElementById('loadResponse').addEventListener('click', function () {
            var blob = new Blob([<?= json_encode($file_client_response) ?>], {type: "application/json;charset=utf-8"});
            saveAs(blob, "rs.json");
        });
        document.getElementById('loadResponsePr').addEventListener('click', function () {
            var blob = new Blob([<?= json_encode($file_client_response_pr) ?>], {type: "application/json;charset=utf-8"});
            saveAs(blob, "rs_pr.json");
        });
        document.getElementById('loadRQ').addEventListener('click', function () {
            var blob = new Blob([<?= json_encode($file_original) ?>], {type: "application/json;charset=utf-8"});
            saveAs(blob, "rq.json");
        });

        document.getElementById('downLoadRawRequest').addEventListener('click', function () {
            Object.keys(fileOriginal).forEach(function (key) {
                if (fileOriginal[key].request) {
                    var blob = new Blob([fileOriginal[key].request], {type: "application/plain;charset=utf-8"});
                    saveAs(blob, `request_${key}_${formattedDate}_{{$inspector->search_id}}.txt`);
                }
            });
        });

        document.getElementById('downloadRawResponse').addEventListener('click', function () {
            Object.keys(fileOriginal).forEach(function (key) {
                if (fileOriginal[key].response) {
                    var blob = new Blob([fileOriginal[key].response], {type: "application/plain;charset=utf-8"});
                    saveAs(blob, `response_${key}_${formattedDate}_{{$inspector->search_id}}.txt`);
                }
            });
        });

    </script>
@endsection
