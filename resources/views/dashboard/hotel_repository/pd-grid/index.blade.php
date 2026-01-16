@extends('layouts.master')
@section('title')
    {{ __('PD Grid') }}
@endsection
@section('content')
    <h2 class="font-semibold">PD Grid</h2>

    <div class="col-span-12">
        <div class="relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
            @livewire('products.pd-grid-table')
        </div>
    </div>
@endsection