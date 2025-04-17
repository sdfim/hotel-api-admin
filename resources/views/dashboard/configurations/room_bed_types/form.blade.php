@extends('layouts.master')
@section('title')
    @if($configRoomBedType->exists)
        {{ __('Edit Bed Type in Room') }}
    @else
        {{ __('Create Bed Type in Room') }}
    @endif
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $configRoomBedType->exists ? $text['edit'] : $text['create'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mb-6">
                                <x-button-back route="{{ route('configurations.room-bed-types.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                        @livewire('configurations.room-bed-types.room-bed-type-form', compact('configRoomBedType'))
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
