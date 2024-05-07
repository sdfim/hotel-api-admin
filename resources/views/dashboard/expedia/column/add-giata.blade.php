<button type="button" onClick="openModal('{{ $getRecord()->property_id }}')"
        class="text-white bg-green-500 border-green-500 btn hover:bg-green-600 focus:ring ring-green-200 focus:bg-green-600"
        data-tw-target="#modal-idlargemodal-{{ $getRecord()->property_id }}">Change
</button>
<div class="relative z-50 hidden modal" id="modal-idlargemodal-{{ $getRecord()->property_id }}"
     aria-labelledby="modal-title"
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
                            Add Giata Mapping to {{ $getRecord()->property_id }}
                        </h3>
                        <button onClick="closeModal('{{ $getRecord()->property_id }}')"
                                class="inline-flex items-center px-2 py-1 text-sm text-gray-400 border-transparent rounded-lg btn hover:bg-gray-50/50 hover:text-gray-900 dark:text-gray-100 ltr:ml-auto rtl:mr-auto dark:hover:bg-zinc-600"
                                type="button">
                            <i class="text-xl text-gray-500 mdi mdi-close dark:text-zinc-100/60"></i>
                        </button>
                    </div>
                    <form action="{{ route('mapping.store') }}" method="POST">
                        <div class="modal-body">
                            @csrf
                            <input hidden name="expedia_id" value="{{ $getRecord()->property_id }}">
{{--                            <input hidden name="giata_last_id"--}}
{{--                                   value="{{ $getRecord()->mapperGiataExpedia[0]->giata_id ?? '' }}">--}}
{{--                            <label for="giata_id">Giata ID</label>--}}
{{--                            <input class="form-control" id="giata_id" type="text" name="giata_id"--}}
{{--                                   value="{{ $getRecord()->mapperGiataExpedia[0]->giata_id ?? '' }}">--}}
                        </div>
                        <!-- Modal footer -->
                        <div
                            class="flex items-center gap-3 p-5 space-x-2 border-t rounded-b border-gray-50 dark:border-zinc-600">
                            <button type="submit"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-500 border border-transparent rounded-md shadow-sm btn hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 sm:w-auto sm:text-sm">
                                Save
                            </button>
                            <button type="button"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm btn dark:text-gray-100 hover:bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-gray-500/30 sm:mt-0 sm:w-auto sm:text-sm dark:bg-zinc-700 dark:border-zinc-600 dark:hover:bg-zinc-600 dark:focus:bg-zinc-600 dark:focus:ring-zinc-700 dark:focus:ring-gray-500/20"
                                    onClick="closeModal('{{ $getRecord()->property_id }}')">Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
