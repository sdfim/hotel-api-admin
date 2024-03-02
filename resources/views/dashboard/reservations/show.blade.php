@extends('layouts.master')
@section('title')
    {{ __('Reservation Details') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}"/>
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6 text-gray-700 dark:text-gray-100">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600 ">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15" x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h6>
            </div>
            @php
                $field = json_decode($reservation->reservation_contains, true);
            @endphp
            <div class="card-body">
                <!-- Back Button -->
                <div class="mb-3 row">
                    <x-button-back route="{{ route('reservations.index') }}" text="Back"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Type and Supplier Column -->
                    <div class="card p-3 rounded">
                        <p><strong>Type:</strong> {{ $field['type'] }}</p>
                        <p><strong>Supplier:</strong> {{ $field['supplier'] }}</p>
                    </div>

                    <!-- Search id, Booking item, Booking id Column -->
                    <div class="card p-3 rounded">
                        <p><strong>Search id:</strong> {{ $field['search_id'] }}</p>
                        <p><strong>Booking item:</strong> {{ $field['booking_item'] }}</p>
                        <p><strong>Booking id:</strong> {{ $field['booking_id'] }}</p>
                    </div>
                </div>

                <!-- Grid for Info, Price, Reservation Contains -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Info Column -->
                    <div class="card p-3 rounded">
                        <div class="border-b mb-3">
                            <h5 class="text-17">Info</h5>
                        </div>

                        <p><strong>ID:</strong> {{ $reservation->id }}</p>
                        <p><strong>Date offload:</strong> {{ $reservation->date_offload ?? "N/A" }}</p>
                        <p><strong>Date of Travel:</strong> {{ $reservation->date_travel}}</p>
                        <p><strong>Passenger Surname:</strong> {{ $reservation->passenger_surname}}</p>
                        <p><strong>Channel:</strong></p>
                        <ul class="!list-disc ml-6">
                            <li><strong>Name:</strong> {{ $reservation->channel->name}}</li>
                            <li><strong>Description:</strong> {{ $reservation->channel->description}}</li>
                        </ul>
                        <p><strong>Total Cost:</strong> {{ $reservation->total_cost }}</p>
                        <p><strong>Create:</strong> {{ $reservation->created_at }}</p>
                        <p><strong>Update:</strong> {{ $reservation->updated_at }}</p>
                    </div>

                    <!-- Price Column -->
                    <div class="card p-3 rounded">
                        <div class="border-b mb-3">
                            <h5 class="text-17">Price</h5>
                        </div>

                        <p><strong>Currency:</strong> {{ $field['price']['currency'] }}</p>
                        <p><strong>Total net:</strong> {{ $field['price']['total_net'] }}</p>
                        <p><strong>Total tax:</strong> {{ $field['price']['total_tax'] }}</p>
                        <p><strong>Total fees:</strong> {{ $field['price']['total_fees'] }}</p>
                        <p><strong>Total price:</strong> {{ $field['price']['total_price'] }}</p>
                        <p><strong>Affiliate service
                                charge:</strong> {{ $field['price']['affiliate_service_charge'] ?? 0 }}
                        </p>
                    </div>

                    <!-- Reservation Contains Column -->
                    <div class="card p-3 rounded">
                        <div class="border-b mb-3">
                            <h5 class="text-17">Reservation Contains</h5>
                        </div>

                        <p><strong>Hotel id:</strong> {{ $field['hotel_id'] }}</p>
                        <p><strong>Hotel name:</strong> {{ $field['hotel_name'] }}</p>
                        <p><strong>Giata room code:</strong> {{ $field['price']['giata_room_code'] }}</p>
                        <p><strong>Giata room name:</strong> {{ $field['price']['giata_room_name'] }}</p>
                        <p><strong>Supplier room name:</strong> {{ $field['price']['supplier_room_name'] }}</p>
                        <p><strong>Per day rate breakdown:</strong> {{ $field['price']['per_day_rate_breakdown'] }}</p>
                    </div>
                </div>

                <!-- Hotel Images Column -->
                <div class="grid grid-cols-1">
                    <div class="card p-3 rounded">
                        <div class="border-b">
                            <h5 class="text-17">Hotel Images</h5>
                        </div>
                        <div class="pt-3">
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
