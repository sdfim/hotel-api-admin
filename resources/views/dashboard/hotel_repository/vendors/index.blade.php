@extends('layouts.master')
@section('title')
    {{ __('Vendors') }}
@endsection
@section('content')
    <div class="breadcrumb-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                {{--                        <li class="breadcrumb-item"><a href="{{ route('supplier-repository.index') }}">Supplier Repository</a></li>--}}
                <li class="breadcrumb-item"><a href="{{ route('vendor-repository.index') }}">Vendors</a></li>
            </ol>
        </nav>
    </div>
    <h2 class="font-semibold">Vendors</h2>
{{--    <x-page-title title="Vendors" pagetitle="index"/>--}}
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                    @livewire('vendors.vendor-table')
                </div>
            </div>
        </div>
    </div>
@endsection
