@php

    $search_id = $getRecord()->search_id;
    $str =  '<a href=' . route('search-inspector.show', $search_id ) .' target="_blank"  class="p-2" style="color: #007bff;">' . $search_id . '</a><br>';

@endphp

{!! $str !!}
