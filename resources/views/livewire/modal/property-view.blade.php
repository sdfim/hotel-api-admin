<?php
$fields = $record->toArray();
$scalarFields = [];
$detailedFieldsData = [];

$ignoredFields = $record->ignoredFields ?? [];
$detailedFields = $record->detailedFields ?? [];
$labelFields = $record->labelFields ?? [];

$tabKeys = ['fields'];
foreach ($fields as $key => $value) {
    if (in_array($key, $ignoredFields)) {
        continue;
    }

    if (in_array($key, $detailedFields)) {
        if (is_array($value) && count($value) > 0 && is_array(reset($value))) {
            foreach ($value as $nestedKey => $nestedValue) {
                $tabKey = "tab_" . md5("{$key}.{$nestedKey}");
                $detailedFieldsData[$tabKey] = [
                    'label' => $labelFields["{$key}.{$nestedKey}"] ?? "{$key}.{$nestedKey}",
                    'value' => $nestedValue
                ];
                $tabKeys[] = $tabKey;
            }
        } else {
            $tabKey = "tab_" . md5($key);
            $detailedFieldsData[$tabKey] = [
                'label' => $labelFields[$key] ?? $key,
                'value' => $value
            ];
            $tabKeys[] = $tabKey;
        }
    } else if (is_array($value) || is_object($value)) {
        $tabKey = "tab_" . md5($key);
        $detailedFieldsData[$tabKey] = [
            'label' => $labelFields[$key] ?? $key,
            'value' => $value
        ];
        $tabKeys[] = $tabKey;
    } else {
        $scalarFields[$key] = $value;
    }
}
?>

<div x-data="{
    activeTab: 'fields',
    editors: {},
    switchTab(tab) {
        this.activeTab = tab;
        this.$nextTick(() => {
            // Инициализация JSONEditor, если его еще нет
            if (!this.editors[tab]) {
                const container = document.getElementById('json-editor-' + tab);
                if (container) {
                    const options = {
                        mode: 'view',
                        collapsed: true
                    };
                    const editor = new JSONEditor(container, options);
                    const data = JSON.parse(document.getElementById('json-data-' + tab).textContent);
                    editor.set(data);
                    this.editors[tab] = editor;
                }
            }
        });
    }
}"
     x-init="document.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el))">
    <div class="border-b mb-4 flex space-x-2 overflow-x-auto whitespace-now-wrap">
        <button type="button"
                class="px-4 py-2"
                :class="{ 'font-bold border-b-2 border-primary': activeTab === 'fields' }"
                @click="switchTab('fields')"
        >Fields</button>
        @foreach($detailedFieldsData as $tabKey => $tabData)
            <button type="button"
                    class="px-4 py-2"
                    :class="{ 'font-bold border-b-2 border-primary': activeTab === '{{ $tabKey }}' }"
                    @click="switchTab('{{ $tabKey }}')"
            >{{ $tabData['label'] }}</button>
        @endforeach
    </div>

    <div x-show="activeTab === 'fields'">
        @if(count($scalarFields) > 0)
            <ul>
                @foreach($scalarFields as $key => $value)
                    <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                @endforeach
            </ul>
        @else
            <p>No scalar fields found.</p>
        @endif
    </div>

    @foreach($detailedFieldsData as $tabKey => $tabData)
        <div x-show="activeTab === '{{ $tabKey }}'">
            <strong>{{ $tabData['label'] }}:</strong>
            <div id="json-editor-{{ $tabKey }}" style="height: 500px;"></div>
            <script type="application/json" id="json-data-{{ $tabKey }}">
                {!! json_encode($tabData['value']) !!}
            </script>
        </div>
    @endforeach

    @if(count($scalarFields) === 0 && count($detailedFieldsData) === 0)
        <p>No fields found.</p>
    @endif
</div>
