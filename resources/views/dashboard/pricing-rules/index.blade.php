@extends('layouts.master')
@section('title')
    {{ __('Pricing Rules') }}
@endsection
@section('content')
    <x-page-title title="Pricing Rules" pagetitle="index"/>
    <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            @livewire('pricing-rules.pricing-rules-table')
    </div>
@endsection
