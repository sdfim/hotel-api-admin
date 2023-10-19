@props([
	'disabled' => false,
	'value' => '',
	'options' => []
])

@php
    $lowerValue = strtolower($value);
@endphp

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500
    focus:ring-indigo-500 rounded-md shadow-sm disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200
    disabled:shadow-none']) !!}>
    <option disabled selected>Select</option>
    @foreach ($options as $key => $option)
        @php
            $lowerKey = strtolower($key)
        @endphp
        <option value="{{$key}}" {{$lowerKey === $lowerValue ? 'selected' : ''}}>{{$option}}</option>
    @endforeach
</select>
