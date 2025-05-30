@php

    $search_id = $getRecord()->search_id;
    $shortSearchId = \App\Livewire\Helpers\ViewHelpers::compressString($search_id);
    $str =  '<a href=' . route('search-inspector.show', $search_id ) .' title="'.$search_id.'" alt="'.$search_id.'" target="_blank" style="color: #007bff;" class="text-sm">' . $shortSearchId . '</a><br>';

@endphp

{!! $str !!}
<x-copy-button-icon value="{{ $search_id }}"></x-copy-button-icon>

