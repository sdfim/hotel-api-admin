<style>
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-x: auto;
        max-width: 100%;
    }
</style>

<div x-data="{
    activeTab: '{{ $activity->event === 'created' ? 'after' : 'before' }}',
    event: '{{ $activity->event }}',
    hasBefore: {{ json_encode(!empty($activity->properties['old'])) }},
    hasAfter: {{ json_encode(!empty($activity->properties['attributes'])) }}
}" @click.stop>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- General Information Column -->
        <div class="bg-blue-50 dark:bg-gray-700 p-5 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold">General Information</h3>
            <p><span class="text-sm font-extrabold italic">Log Name:</span> <span class="text-lg font-bold">{{ $activity->log_name }}</span></p>
            <p><span class="text-sm font-extrabold italic">Description:</span> <span class="text-lg font-bold">{{ $activity->description }}</span></p>
            <p><span class="text-sm font-extrabold italic">Causer Name:</span> <span class="text-lg font-bold">{{ $activity->causer?->name }}</span></p>
            <p><span class="text-sm font-extrabold italic">Causer Email:</span> <span class="text-lg font-bold">{{ $activity->causer?->email }}</span></p>

            <div x-data="{ isOpenAdditional: false }" class="mt-6" @click.outside="$event.stopPropagation()">
                <button
                    type="button"
                    @click.prevent.stop="isOpenAdditional = !isOpenAdditional"
                    class="font-semibold text-blue-500">
                    Additional Information
                </button>
                <div
                    x-show="isOpenAdditional"
                    x-cloak
                    @click.stop.prevent>
                    <p><span class="font-semibold">ID:</span> {{ $activity->id }}</p>
                    <p><span class="font-semibold">Event:</span> {{ $activity->event }}</p>
                    <p><span class="font-semibold">Subject Type:</span> {{ $activity->subject_type }}</p>
                    <p><span class="font-semibold">Subject ID:</span> {{ $activity->subject_id }}</p>
                    <p><span class="font-semibold">Causer Type:</span> {{ $activity->causer_type }}</p>
                    <p><span class="font-semibold">Causer ID:</span> {{ $activity->causer_id }}</p>

                    <p class="mt-2 font-semibold">Roles:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($activity->causer?->roles ?? [] as $role)
                            <li>{{ $role->name }}</li>
                        @endforeach
                    </ul>

                    <p class="mt-2 font-semibold">Teams:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($activity->causer?->teams ?? [] as $team)
                            <li>{{ $team->name }}</li>
                        @endforeach
                    </ul>

                    <p class="mt-2"><span class="font-semibold">Batch UUID:</span> {{ $activity->batch_uuid }}</p>
                    <p><span class="font-semibold">Created At:</span> {{ $activity->created_at }}</p>

                </div>
            </div>
        </div>

        <!-- Tabbed Section (2 Columns) -->
        <div class="col-span-2">
            <!-- Tabs Navigation -->
            <div class="flex border-b border-gray-300 dark:border-gray-600">
                <button x-show="event !== 'created' && hasBefore" @click.prevent="activeTab = 'before'" :class="{'border-blue-500 text-blue-500': activeTab === 'before'}" class="px-4 py-2 border-b-2">Before</button>
                <button x-show="event !== 'deleted' && hasAfter" @click.prevent="activeTab = 'after'" :class="{'border-blue-500 text-blue-500': activeTab === 'after'}" class="px-4 py-2 border-b-2">After</button>
                <button x-show="event !== 'created' && event !== 'deleted' && hasBefore && hasAfter" @click.prevent="activeTab = 'compare'" :class="{'border-blue-500 text-blue-500': activeTab === 'compare'}" class="px-4 py-2 border-b-2">Compare</button>
            </div>

            <!-- Before Changes -->
            <div x-show="activeTab === 'before' && event !== 'created'" x-cloak class="p-5">
                <h3 class="text-lg font-semibold">Before Changes</h3>
                <pre class="bg-blue-50 dark:bg-gray-700 p-5 rounded-lg shadow-md">{{ json_encode($activity->properties['old'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <!-- After Changes -->
            <div x-show="activeTab === 'after'" x-cloak class="p-5">
                <h3 class="text-lg font-semibold">After Changes</h3>
                <pre class="bg-blue-50 dark:bg-gray-700 p-5 rounded-lg shadow-md">{{ json_encode($activity->properties['attributes'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <!-- Compare Changes -->
            <div x-show="activeTab === 'compare'" x-cloak class="p-5">
                <h3 class="text-lg font-semibold">Comparison</h3>
                <table class="w-full border-collapse border border-gray-300 dark:border-gray-700 text-sm">
                    <thead>
                    <tr class="bg-gray-200 dark:bg-gray-600">
                        <th class="border px-2 py-1">Field</th>
                        <th class="border px-2 py-1">Before</th>
                        <th class="border px-2 py-1">After</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach(($activity->properties['old'] ?? []) as $key => $oldValue)
                        @php
                            $newValue = $activity->properties['attributes'][$key] ?? null;
                        @endphp
                        @if ($oldValue !== $newValue)
                            <tr>
                                <td class="border px-2 py-1">{{ $key }}</td>
                                <td class="border px-2 py-1">{{ is_array($oldValue) ? json_encode($oldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $oldValue }}</td>
                                <td class="border px-2 py-1">{{ is_array($newValue) ? json_encode($newValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $newValue }}</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
