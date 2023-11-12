@php
    $search_id = $getRecord()->search_id;
	$str =  '<a href=' . route('search-inspector.show', $search_id ) .' target="_blank" style="color: #007bff;">' . $search_id . '</a>';
@endphp
{!! $str !!}