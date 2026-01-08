@php
    $file_response = Storage::get($getRecord()->response_path);
    if($file_response == ''){
        $file_response = json_encode([]);
    }
@endphp
<button type="button" onClick="openModal('{{ $getRecord()->report_id }}','request', {{$file_response}})"
        class="text-white bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600"
        data-tw-target="#modal-idlargemodal-{{ $getRecord()->report_id }}-request">JSON
</button>
<style>
    json-viewer {
        padding: 10px;
        text-wrap: wrap;
    }
</style>
<div class="relative z-50 hidden modal" id="modal-idlargemodal-{{ $getRecord()->report_id }}-request"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="absolute inset-0 transition-opacity bg-black bg-opacity-50 modal-overlay"></div>
        <div class="p-4 mx-auto animate-translate max-w-5xl">
            <div
                class="relative overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-600">
                <div class="bg-white dark:bg-zinc-700">
                    <div class="flex items-center p-4 border-b rounded-t border-gray-50 dark:border-zinc-600">
                        <h3 class="text-xl font-semibold text-gray-100 dark:text-gray-100 ">
                            {{ $getRecord()->report_id }}
                        </h3>
                        <button onClick="closeModal('{{ $getRecord()->report_id }}','request')"
                                class="inline-flex items-center px-2 py-1 text-sm text-gray-400 border-transparent rounded-lg btn hover:bg-gray-50/50 hover:text-gray-900 dark:text-gray-100 ltr:ml-auto rtl:mr-auto dark:hover:bg-zinc-600"
                                type="button">
                            <i class="text-xl text-gray-500 mdi mdi-close dark:text-zinc-100/60"></i>
                        </button>
                    </div>
                    <div class="modal-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                        Request:
                        <div id="actions-toolbar">
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="expand-response-{{$getRecord()->report_id}}">Expand All
                            </button>
                            <button
                                class="btn text-white bg-gray-500 border-gray-500 hover:bg-gray-600 hover:border-gray-600 focus:bg-gray-600 focus:border-gray-600 focus:ring focus:ring-gray-500/30 active:bg-gray-600 active:border-gray-600"
                                id="collapse-response-{{$getRecord()->report_id}}">Collapse All
                            </button>
                            <input
                                class="rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-maintheme-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                id="search-response-{{$getRecord()->report_id}}" placeholder="search"></input>
                        </div>
                        <json-viewer id="json-response-{{$getRecord()->report_id}}" style="font-size:0.8em">
                        </json-viewer>
                    </div>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center gap-3 p-5 space-x-2 border-t rounded-b border-gray-50 dark:border-zinc-600">
                        <button type="button"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm btn dark:text-gray-100 hover:bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-gray-500/30 sm:mt-0 sm:w-auto sm:text-sm dark:bg-zinc-700 dark:border-zinc-600 dark:hover:bg-zinc-600 dark:focus:bg-zinc-600 dark:focus:ring-zinc-700 dark:focus:ring-gray-500/20"
                                onClick="closeModal('{{ $getRecord()->report_id }}','request')">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
