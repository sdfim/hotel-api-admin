@extends('layouts.master')
@section('title')
    {{ __('Reservations') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="pull-left">
                                <h2 x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h2>
                            </div>
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('reservations.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>ID:</strong>
                                {{ $reservation->id }}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Date offload:</strong>
                                {{ $reservation->date_offload ?? "N/A" }}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Date of Travel:</strong>
                                {{ $reservation->date_travel}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Passenger Surname:</strong>
                                {{ $reservation->passenger_surname}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Contains:</strong>
                                <br>
                                <ul class="ml-15">
                                    <li><strong>Name:</strong> {{ $reservation->contains->name}}</li>
                                    <li><strong>Description:</strong> {{ $reservation->contains->description}}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Channel:</strong>
                                <br>
                                <ul class="ml-15">
                                    <li><strong>Name:</strong> {{ $reservation->channel->name}}</li>
                                    <li><strong>Description:</strong> {{ $reservation->channel->description}}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Total Cost:</strong>
                                {{ $reservation->total_cost}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Create:</strong>
                                {{ $reservation->created_at }}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Update:</strong>
                                {{ $reservation->updated_at }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
