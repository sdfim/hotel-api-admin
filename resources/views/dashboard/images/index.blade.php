@extends('layouts.master')
@section('title')
    {{ __('Hotel Images') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Hotel Images" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('hotel-images.hotel-images-table', ['productId' => $productId ?? null])
        </div>
    </div>
@endsection
