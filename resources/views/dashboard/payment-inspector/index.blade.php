@extends('layouts.master')
@section('title')
    {{ __('Payment Intents') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Payment Intents" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('inspectors.api-booking-payment-init-table')
        </div>
    </div>
@endsection
