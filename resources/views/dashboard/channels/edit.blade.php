@extends('layouts.master')
@section('title')
    {{ __('General channels') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Edit Channel</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2>Edit Channel</h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('channels.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="relative px-5 py-3 border-2 bg-red-50 text-red-700 border-red-100 rounded">
                            <p><strong>Whoops!</strong>There were some problems with your input.</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('channels.update', $channel->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="col-span-12 lg:col-span-6">
                            <div class="mb-4">
                                <label for="example-text-input"
                                       class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Name</label>
                                <input
                                    class="w-full rounded border-gray-100 placeholder:text-sm focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                    type="text" name="name" value="{{ $channel->name }}" placeholder="Name"
                                    id="example-text-input">
                            </div>
                            <div class="mb-4">
                                <label for="example-text-input"
                                       class="block font-medium text-gray-700 dark:text-gray-100 mb-2">Description:</label>
                                <input
                                    class="w-full rounded border-gray-100 placeholder:text-sm focus:border focus:border-violet-500 focus:ring-0 dark:bg-zinc-700/50 dark:border-zinc-600 dark:placeholder:text-zinc-100 dark:text-zinc-100"
                                    type="text" name="description" value="{{ $channel->description }}"
                                    placeholder="Description" id="example-search-input">
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
