@extends('dashboard.suppliers.layout')
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Edit Suppliers</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2>Edit Suppliers</h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('suppliers.index') }}" text="Back"
                                    style="additional-styles" />
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="relative px-5 py-3 border-2 bg-red-50 text-red-700 border-red-100 rounded">
                            <p> <strong>Whoops!</strong>There were some problems with your input.</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('suppliers.update', $suppliers->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-label for="name" class="dark:text-gray-100" value="{{ __('Name') }}" />
                            <x-input id="name" name="name" value="{{ $suppliers->name }}" placeholder="Name"
                                type="text"
                                class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                wire:model="state.name" autocomplete="name" />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <x-label for="description" class="dark:text-gray-100" value="{{ __('Description') }}" />
                            <x-input id="description" name="description" value="{{ $suppliers->description }}"
                                placeholder="Description" type="text"
                                class="mt-1 block w-full dark:bg-zinc-700 dark:border-transparent dark:text-gray-100"
                                wire:model="state.description" autocomplete="description" />
                            <x-input-error for="description" class="mt-2" />
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
@endsection
