@extends('layouts.master')
@section('title')
    {{ __('Expedia') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Expedia" pagetitle="index"/>
    
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    @livewire('expedia-table')
                </div>
            </div>
        </div>
    </div>
@endsection
