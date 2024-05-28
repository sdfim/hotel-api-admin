<button type="button" onClick="openModal('{{ $getRecord()->id }}','request')"
        class="text-white bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600 ml-3"
        data-tw-target="#modal-idlargemodal-{{ $getRecord()->id }}-request">View request
</button>
<div class="relative z-50 hidden modal" id="modal-idlargemodal-{{ $getRecord()->id }}-request"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="absolute inset-0 transition-opacity bg-black bg-opacity-50 modal-overlay"></div>
        <div class="p-4 mx-auto animate-translate sm:max-w-xl">
            <div
                class="relative overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-600">
                <div class="bg-white dark:bg-zinc-700">
                    <div class="flex items-center p-4 border-b rounded-t border-gray-50 dark:border-zinc-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 ">
                            {{ $getRecord()->booking_id }}
                        </h3>
                        <button onClick="closeModal('{{ $getRecord()->id }}','request')"
                                class="inline-flex items-center px-2 py-1 text-sm text-gray-400 border-transparent rounded-lg btn hover:bg-gray-50/50 hover:text-gray-900 dark:text-gray-100 ltr:ml-auto rtl:mr-auto dark:hover:bg-zinc-600"
                                type="button">
                            <i class="text-xl text-gray-500 mdi mdi-close dark:text-zinc-100/60"></i>
                        </button>
                    </div>
                    <div class="modal-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                        Request:
                        <pre>{{json_encode(json_decode($getRecord()->request), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    <!-- Modal footer -->
                    <div
                        class="flex items-center gap-3 p-5 space-x-2 border-t rounded-b border-gray-50 dark:border-zinc-600">
                        <button type="button"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm btn dark:text-gray-100 hover:bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-gray-500/30 sm:mt-0 sm:w-auto sm:text-sm dark:bg-zinc-700 dark:border-zinc-600 dark:hover:bg-zinc-600 dark:focus:bg-zinc-600 dark:focus:ring-zinc-700 dark:focus:ring-gray-500/20"
                                onClick="closeModal('{{ $getRecord()->id }}','request')">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
