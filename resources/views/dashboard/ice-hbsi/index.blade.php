@extends('layouts.master')
@section('title')
{{ __('ICE Portal') }}
@endsection
@section('content')
<!-- -->
<x-page-title title="ICE Portal" pagetitle="index"/>

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body relative overflow-x-auto text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                @livewire('ice-hbsi-property-table')
            </div>
        </div>
    </div>
</div>
@endsection
