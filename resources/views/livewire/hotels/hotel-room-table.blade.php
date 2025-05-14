<div>
    <div id="hotel-room-table" ax-load=""
         ax-load-src="http://localhost:8008/js/filament/tables/components/table.js?v=3.2.131.0" x-data="table"
         class="fi-ta">
        <div
            class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
            <div x-bind:hidden="! (true || (selectedRecords.length && 0))"
                 x-show="true || (selectedRecords.length && 0)"
                 class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
                {{ $this->table }}
            </div>
        </div>
    </div>

    <div
        x-data="{ open: false, showOptions: false }"
        x-init="
        window.addEventListener('open-merge-confirmation-modal', () => { open = true });
    "
    >
        <div x-show="open" class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
            <div class="absolute inset-0 bg-black opacity-50" @click="open = false"></div>
            <div class="bg-white p-6 rounded shadow-lg relative z-10 max-w-md w-full">
                <h2 class="text-lg font-semibold">Merge Confirmation</h2>
                <p>Are you sure you want to merge room '{{ $fromRoom?->name }}' into '{{ $toRoom?->name }}'?</p>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="open = false" class="mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="$wire.confirmMerge(); open = false"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('#hotel-room-table tbody');
            if (tableBody) {
                new Sortable(tableBody, {
                    animation: 150,
                    handle: 'td.fi-table-cell-id',
                    onMove: function (evt) {
                        return false; // Block movement
                    },
                    onStart: function (evt) {
                        // Save the original ID when dragging starts
                        evt.item.dataset.fromId = evt.item.querySelector('td.fi-table-cell-id').textContent.trim();
                        evt.item.dataset.initialClientY = evt.originalEvent.clientY;
                    },
                    onEnd: function (evt) {
                        const fromElement = evt.item.querySelector('td.fi-table-cell-id');
                        const fromId = evt.item.dataset.fromId; // Take the saved ID
                        const initialClientY = parseFloat(evt.item.dataset.initialClientY);

                        // Get coordinates from the original event
                        const originalEvent = evt.originalEvent;
                        const x = originalEvent?.clientX;
                        const y = originalEvent?.clientY;

                        let toElement = null;
                        if (x !== undefined && y !== undefined && !isNaN(x) && !isNaN(y)) {
                            // Temporarily hide the dragged element to avoid interfering with target detection
                            evt.item.style.display = 'none';
                            const droppedOverElement = document.elementFromPoint(x, y);
                            let targetRow = droppedOverElement?.closest('tr');

                            // Check if the mouse moved down
                            if (originalEvent.clientY > initialClientY && targetRow) {
                                targetRow = targetRow.previousElementSibling;
                            }

                            toElement = targetRow?.querySelector('td.fi-table-cell-id');
                            evt.item.style.display = ''; // Restore visibility
                        }

                        if (fromElement && toElement) {
                            const toId = toElement.textContent.trim();
                            console.log('fromId:', fromId, 'toId:', toId);

                            // Call the server method for "merging"
                        @this.call('mergeRooms', fromId, toId);
                        } else {
                            console.error('Required elements not found', {fromElement, toElement, x, y});
                        }
                    },
                });
            } else {
                console.error('Table body not found');
            }
        });
    </script>
@endpush
