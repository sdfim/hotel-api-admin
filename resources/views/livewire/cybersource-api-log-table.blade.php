<div>
    {{ $this->table }}
</div>

@if($showModal && $selectedLog)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
            <button wire:click="$set('showModal', false)"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
            <h2 class="text-xl font-bold mb-4">Cybersource API Log #{{ $selectedLog->id }}</h2>
            <div class="space-y-2">
                <div><strong>Method:</strong> {{ $selectedLog->method }}</div>
                <div><strong>Payment Intent ID:</strong> {{ $selectedLog->payment_intent_id }}</div>
                <div><strong>Status Code:</strong> {{ $selectedLog->status_code }}</div>
                <div><strong>Created At:</strong> {{ $selectedLog->created_at }}</div>
                <div><strong>Updated At:</strong> {{ $selectedLog->updated_at }}</div>
                <div><strong>Direction:</strong>
                    <pre
                        class="bg-gray-100 p-2 rounded text-xs">{{ json_encode($selectedLog->direction, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div><strong>Payload:</strong>
                    <pre
                        class="bg-gray-100 p-2 rounded text-xs">{{ json_encode($selectedLog->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div><strong>Response:</strong>
                    <pre
                        class="bg-gray-100 p-2 rounded text-xs">{{ json_encode($selectedLog->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
@endif