@extends('layouts.master')
@section('title')
    {{ __('Image Galleries') }}
@endsection

@section('css')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.css"
    />
@endsection

@section('content')
    <!-- -->
    <x-page-title title="Image Galleries" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('image-galleries.image-galleries-table', ['productId' => null])
        </div>
    </div>
@endsection
