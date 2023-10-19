@props([
    'type' => 'success',
    'message' => '',
    'timeout' => 5000
])

@php
    $classes = $type === 'success'
    ? 'from-green-500 to-green-400'
    : 'from-red-500 to-red-400';
    if (!$message) {
		$message = $type === 'success' ?__('Operation successful') : __('Operation failed');
    }
@endphp

<div class="flex justify-center py-12"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, {{$timeout}})"
>
    <div class="flex bg-white flex-row shadow-md border border-gray-100 rounded-lg overflow-hidden md:w-5/12">
        <div class="flex w-3 bg-gradient-to-t {{$classes}}"></div>
        <div class="flex-1 p-3">
            <h1 class="md:text-xl text-gray-600">{{ $type === 'success' ? __('Success') : __('Error')}}</h1>
            <p class="text-gray-400 text-xs md:text-sm font-light">{{$message}}</p>
        </div>
        <div class="cursor-pointer border-l hover:bg-gray-50 border-gray-100 px-4 flex place-items-center"
             @click="show = false">
            <p class="text-gray-400 text-xs">{{__('Close')}}</p>
        </div>
    </div>
</div>
