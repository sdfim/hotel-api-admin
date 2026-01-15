<div class="p-4 space-y-6 overflow-x-auto">
    @php
        $decoded = json_decode($payload, true);
        $commandDetails = null;
        $unserializeError = null;

        if ($decoded && isset($decoded['data']['command'])) {
            try {
                $commandStr = $decoded['data']['command'];
                $unserialized = unserialize($commandStr);

                if ($unserialized) {
                    $rawArray = (array) $unserialized;
                    $commandDetails = [];

                    foreach ($rawArray as $key => $value) {
                        $cleanKey = str_replace("\0", "", $key);
                        $cleanKey = str_replace("*", "", $cleanKey);
                        if (str_contains($cleanKey, '\\')) {
                            $parts = explode('\\', $cleanKey);
                            $cleanKey = end($parts);
                        }
                        $commandDetails[$cleanKey] = $value;
                    }
                }
            } catch (\Throwable $e) {
                $unserializeError = $e->getMessage();
            }
        }

        $displayPayload = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $payload;
    @endphp

    @if($commandDetails)
        <div class="rounded-lg border border-primary-100 dark:border-primary-900 overflow-hidden">
            <div class="bg-primary-50 dark:bg-primary-900/30 px-4 py-2 border-b border-primary-100 dark:border-primary-900">
                <h4 class="text-sm font-semibold text-gray-200 dark:text-gray-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Job Data (Unserialized)
                </h4>
            </div>
            <div class="p-0">
                <pre class="p-4 text-xs font-mono bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">{{ json_encode($commandDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif

    @if($unserializeError)
        <div class="bg-warning-50 dark:bg-warning-950 p-4 rounded-lg border border-warning-200 dark:border-warning-900">
            <p class="text-xs text-warning-800 dark:text-warning-300 font-medium">Warning: Could not parse command details</p>
            <p class="text-[10px] text-warning-600 dark:text-warning-400 mt-1">{{ $unserializeError }}</p>
        </div>
    @endif

    <div>
        <h4 class="text-xs font-bold mb-2 text-gray-500 uppercase tracking-wider">Full Raw Payload</h4>
        <pre class="whitespace-pre-wrap break-all bg-gray-50 p-4 rounded-lg text-[10px] dark:bg-gray-800 dark:text-gray-400 font-mono border border-gray-200 dark:border-gray-700 shadow-sm">{{ $displayPayload }}</pre>
    </div>
</div>
