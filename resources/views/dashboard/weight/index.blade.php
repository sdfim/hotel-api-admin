@extends('layouts.master')
@section('title')
    {{ __('Weight') }}
@endsection
@section('content')
    <!-- -->
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Weight</h2>
            </div>

            <div class="mt-6 mb-6">
                <a class="btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600"
                   href="{{ route('weight.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
            </div>

        </div>
    </div>
    @if ($message = Session::get('success'))
        <div
            class="relative flex items-center px-5 py-2 border-2 text-green-500 border-green-500 rounded alert-dismissible">
            <p>{{ $message }}</p>
            <button class="alert-close ltr:ml-auto rtl:mr-auto text-green-400 text-lg"><i class="mdi mdi-close"></i>
            </button>
        </div>
    @endif
    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    @livewire('weights-table')

                </div>
            </div>
        </div>
    </div>
@endsection
