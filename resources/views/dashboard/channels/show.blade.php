@extends('layouts.master')
@section('title')
    {{ __('General channels') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['show'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('channels.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="mt-10 sm:mt-0">
                        <strong>Name:</strong>
                        {{ $channel->name }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Description:</strong>
                        {{ $channel->description }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Access Token:</strong>
                        {{ $channel->access_token }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Create:</strong>
                        {{ $channel->created_at }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Update:</strong>
                        {{ $channel->updated_at }}
                    </div>
                    <x-section-border/>
                </div>
            </div>
        </div>
    </div>
@endsection
