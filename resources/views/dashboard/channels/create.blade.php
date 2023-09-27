@extends('layouts.master')
@section('title')
    {{ __('General channels') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Add New Channel</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2>Add New Channel</h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('channels.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong>
                            <p>There were some problems with your input.</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('channels.store') }}" method="POST">
                        @csrf
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <label for="example-text-input"
                                       class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Name</label>
                                <input
                                    class="w-full rounded border-gray-100 placeholder:text-sm focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                    type="text" name="name" placeholder="Name" id="example-text-input">
                            </div>
                            <div class="mb-4">
                                <label for="example-text-input"
                                       class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Description:</label>
                                <input
                                    class="w-full rounded border-gray-100 py-2.5 text-sm text-gray-500 focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:text-zinc-100"
                                    type="text" name="description" placeholder="Description" id="example-search-input">
                            </div>
                            <div class="mt-6">
                                <x-button>
                                    Submit
                                </x-button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
