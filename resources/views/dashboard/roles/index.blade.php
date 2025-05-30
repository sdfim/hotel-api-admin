@extends('layouts.master')
@section('title')
    {{ __('Roles') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Roles" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('roles.roles-table')
        </div>
    </div>
@endsection
