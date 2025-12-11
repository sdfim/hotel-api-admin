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
                    <div class="card p-3 rounded col-span-1 md:col-span-2 lg:col-span-2">
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
                        <p><strong>Channel:</strong>{{ $reservation->channel->name}}</p>
                        <p><strong>Total Cost:</strong> {{ $reservation->total_cost }}</p>
                        <p><strong>Create:</strong> {{ $reservation->created_at }}</p>
                    </div>

                    <!-- Price Column -->
                    <div class="card p-3 rounded">
                        <div class="border-b mb-3">
                            <h5 class="text-17">Price</h5>
                        </div>

                        <p><strong>Currency:</strong> {{ Arr::get($field, 'price.currency', 'USD') }}</p>
                        <p><strong>Total net:</strong> {{ Arr::get($field, 'price.total_net', 0) }}</p>
                        <p><strong>Total tax:</strong> {{ Arr::get($field, 'price.total_tax', 0) }}</p>
                        <p><strong>Total fees:</strong> {{ Arr::get($field, 'price.total_fees', 0) }}</p>
                        <p><strong>Total price:</strong> {{ Arr::get($field, 'price.total_price', 0) }}</p>
                        <p><strong>Markup:</strong> {{ Arr::get($field, 'price.markup', 0) }}</p>
                        <p><strong>Paid:</strong> {{ $reservation->paid ?? 0 }}</p>
                    </div>

                    <!-- Reservation Contains Column -->
                    <div class="card p-3 rounded">
                        <div class="border-b mb-3">
                            <h5 class="text-17">Reservation Contains</h5>
                        </div>

                        <p><strong>Hotel id:</strong> {{ $field['hotel_id'] }}</p>
                        <p><strong>Hotel name:</strong> {{ $field['hotel_name'] }}</p>
                        <p><strong>Supplier room name:</strong> {{ $field['price']['supplier_room_name'] }}</p>
                        <p><strong>Supplier rate name:</strong> {{ $field['price']['rate_name'] ??  $field['price']['rate_code'] ?? ''}}</p>
                        <p><strong>Advisor email:</strong> {{ $advisorEmail ?? 'N/A' }}</p>
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
                                @if ($images)
                                    @foreach($images as $imageNumber => $image)
                                        @php
                                            $imageUrl = Str::startsWith($image, ['http://', 'https://']) ? $image : Storage::url($image);
                                        @endphp
                                        <a href="{{ $imageUrl }}" class="reservation-show-glightbox mr-1 mb-1">
                                            <img class="w-24 h-24 cursor-pointer animate-draw-attention"
                                                 src="{{ $imageUrl }}" alt="Image {{ $imageNumber }}">
                                        </a>
                                    @endforeach
                                @endif

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
