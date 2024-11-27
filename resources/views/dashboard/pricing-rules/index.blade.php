@extends('layouts.master')
@section('title')
    {{ __('Pricing Rules') }}
@endsection
@section('content')
    <x-page-title title="Pricing Rules" pagetitle="index"/>
    <div class="card dark:bg-zinc-800 dark:border-zinc-600">
        <div class="card-body relative overflow-x-auto">
            @livewire('pricing-rules.pricing-rules-table')
        </div>
    </div>
@endsection
