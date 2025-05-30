@extends('layouts.master')
@section('title')
    {{ __('Insurance Restrictions') }}
@endsection
@section('content')
    <x-page-title title="Insurance Restrictions" pagetitle="index"/>
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('insurance.restrictions-table', ['viewAll' => $viewAll ?? true])
        </div>
    </div>
@endsection
