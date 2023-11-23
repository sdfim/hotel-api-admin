@extends('layouts.master')
@section('title')
    {{ __('Reservation Details') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
    <style>
        .h-100{
            height: 100%;
        }
        .w-100{
            width: 100%;
        }
        .gr-t-3{
            grid-template-columns: 1fr 1fr 3fr;
            grid-column-gap: 15px;
        }
        .gr-t-2{
            grid-template-columns: 1fr 1fr;
            grid-column-gap: 15px;
        }
        .gr-t-32{
            grid-template-columns: 1fr 1fr 1fr;
            grid-column-gap: 15px;
        }
        @media screen and (max-width: 775px) {
            .gr-t-3{
                grid-template-columns: 1fr;
                grid-column-gap: 15px;
            }
            .gr-t-2{
                grid-template-columns: 1fr;
                grid-column-gap: 15px;
            }
            .gr-t-32{
                grid-template-columns: 1fr;
                grid-column-gap: 15px;
                grid-row-gap: 15px;
            }
        }

    </style>
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['show'] }}' }"
                    x-text="message"></h6>
            </div>
            @php
                $field = json_decode($reservation->reservation_contains, true);
               
            @endphp
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <div class="mb-3 row">
                        <x-button-back route="{{ route('reservations.index') }}" text="Back"/>
                    </div>
                    <div class="grid grid-flow-col auto-cols-max gap-5">
						<div class="card p-2 rounded bg-sky-500 col-span-1">
							<p class="text-white/60">Type</p>
							<h5 class="mt-1 text-white text-17">{{$field['type']}}</h5>
							<p class="text-white/60">Supplier</p>
							<h5 class="mt-1 text-white text-17">{{$field['supplier']}}</h5>
						</div>
						<div class="card p-2 rounded bg-sky-500 col-span-2">
							<p class="mt-1 text-white/60">Search id:
								<span class="text-white text-17"><b>{{$field['search_id']}}</b></span>
							</p>
							<p class="mt-2 text-white/60">Booking item:
								<span class="text-white text-17"><b>{{$field['booking_item']}}</b></span>
							</p>
							<p class="mt-2 text-white/60">Booking id:
								<span class="text-white text-17"><b>{{$field['booking_id']}}</b></span>
							</p>
						</div>
					</div>

                    <div class="grid gr-t-32">
                            <div class="">
                                <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                    <div class="p-3 border-b border-sky-500">
                                        <h5 class="text-sky-500 text-17">Info</h5>
                                    </div>
                                    <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                                        <p class="text-gray-700 mt-2 dark:text-zinc-100">
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
                                                    <strong>Channel:</strong>
                                                    <ul class="!list-disc ml-6">
                                                        <li><strong>Name:</strong> {{ $reservation->channel->name}}</li>
                                                        <li><strong>Description:</strong> {{ $reservation->channel->description}}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Total Cost:</strong>
                                                    {{ $reservation->total_cost }}
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
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                    <div class="p-3 border-b border-sky-500">
                                        <h5 class="text-sky-500 text-17">Price</h5>
                                    </div>
                                    <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                                        <p class="text-gray-700 mt-2 dark:text-zinc-100">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Currency:</strong>
                                                    {{ $field['price']['currency'] }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Total net:</strong>
                                                    {{ $field['price']['total_net'] }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Total tax:</strong>
                                                    {{ $field['price']['total_tax'] }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Total fees:</strong>
                                                    {{ $field['price']['total_fees'] }}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Total price:</strong>
                                                    {{ $field['price']['total_price'] }}
                                                </div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                    <div class="p-3 border-b border-sky-500">
                                        <h5 class="text-sky-500 text-17">Reservation Contains</h5>
                                    </div>
                                    <div class="card-body text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">
                                        <p class="text-gray-700 mt-2 dark:text-zinc-100">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Hotel id:</strong>
                                                    {{ $field['hotel_id']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Hotel name:</strong>
                                                    {{ $field['hotel_name']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Giata room code:</strong>
                                                    {{ $field['price']['giata_room_code']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Giata room name:</strong>
                                                    {{ $field['price']['giata_room_name']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Supplier room name:</strong>
                                                    {{ $field['price']['supplier_room_name']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Per day rate breakdown:</strong>
                                                    {{ $field['price']['per_day_rate_breakdown']}}
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong>Affiliate service charge:</strong>
                                                    {{ $field['price']['affiliate_service_charge']}}
                                                </div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-span-12 xl:col-span-12 w-100 mt-10">
                            <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                <div class="p-3 border-b border-sky-500">
                                    <h5 class="text-sky-500 text-17">Hotel Images</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-gray-700 mt-2 dark:text-zinc-100">
                                        @php
                                            $record = json_decode($reservation->reservation_contains);
                                            $images = [];
                                            if(isset($record->hotel_images)){
                                                $images = json_decode($record->hotel_images);
                                            } else {
                                                echo 'Nothing to show';
                                            }
                                        @endphp

                                        <div class="flex flex-wrap">
                                            @foreach($images as $imageNumber => $image)
                                                <a href="{{ $image }}" class="reservation-show-glightbox mr-1 mb-1">
                                                    <img class="w-24 h-24 cursor-pointer animate-draw-attention"
                                                        src="{{ $image }}" alt="Image {{ $imageNumber }}">
                                                </a>
                                            @endforeach
                                        </div>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <script type="module">
        GLightbox({
            selector: '.reservation-show-glightbox',
        });
    </script>
@endsection
