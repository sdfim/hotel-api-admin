@extends('layouts.master')
@section('title')
    {{ __('Giata') }}
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
                                <x-button-back route="{{ route('giata.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="mt-10 sm:mt-0">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Code:</strong>
                                {{ $giata->code }}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Last updated:</strong>
                                {{$giata->last_updated}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Name:</strong>
                                {{$giata->name}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Chain:</strong>
                                <pre>{{json_encode($giata->chain, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>City:</strong>
                                <pre>{{$giata->city}}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Locale:</strong>
                                <pre>{{$giata->locale }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Address:</strong>
                                <pre>{{json_encode($giata->address, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Phone:</strong>
                                <pre>{{json_encode($giata->phone, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Position:</strong>
                                <pre>{{json_encode($giata->position, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>URL:</strong>
                                <pre>{{$giata->url }}</pre>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Cross references:</strong>
                                <pre>{{json_encode($giata->cross_references, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                    <x-section-border/>
                </div>
                
            </div>
        </div>
    </div>
@endsection
@section('scripts')
   
@endsection