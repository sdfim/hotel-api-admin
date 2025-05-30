@extends('layouts.master')
@section('title')
    {{ __('Users') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="Users" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            @livewire('users.users-table')
        </div>
    </div>
@endsection
