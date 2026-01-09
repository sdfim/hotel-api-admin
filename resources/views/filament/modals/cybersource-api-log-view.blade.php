@php
    if (!function_exists('prettyJson')) {
        function prettyJson($value)
        {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
                return $value;
            }
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
@endphp
<div class="space-y-2">
    <div><strong>Method:</strong> {{ $log->method }}</div>
    <div><strong>Payment Intent ID:</strong> {{ $log->payment_intent_id }}</div>
    <div><strong>Status Code:</strong> {{ $log->status_code }}</div>
    <div><strong>Created At:</strong> {{ $log->created_at }}</div>
    <div><strong>Updated At:</strong> {{ $log->updated_at }}</div>
    <div><strong>Direction (Frontend RQ):</strong>
        <pre class="p-2 rounded text-xs">{{ prettyJson($log->direction) }}</pre>
    </div>
    <div><strong>Payload:</strong>
        <pre class="p-2 rounded text-xs">{{ prettyJson($log->payload) }}</pre>
    </div>
    <div><strong>Response:</strong>
        <pre class="p-2 rounded text-xs">{{ prettyJson($log->response) }}</pre>
    </div>
</div>