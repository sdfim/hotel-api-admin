@extends('layouts.master')
@section('title')
    {{ __('General channels') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="General channels" pagetitle="index" />
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="mt-6 mb-6">
                <a class="btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600"
                    href="{{ route('channels.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
            </div>

        </div>
    </div>
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    @livewire('channels-table')
                </div>
            </div>
        </div>
    </div>
@endsection
