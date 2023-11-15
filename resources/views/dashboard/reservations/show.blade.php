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
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mt-6 mb-6">
                                <x-button-back route="{{ route('reservations.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-between">
                        <div class="col-span-12 xl:col-span-4">
                            <div class="card p-5 rounded bg-sky-500 border-sky-500">
                                <h5 class="mb-3 text-white text-17">Search id</h5>
                                <p class="text-white/60">{{$field['search_id']}}</p>
                            </div>
                        </div>
                        <div class="col-span-12 xl:col-span-4">
                            <div class="card p-5 rounded bg-sky-500 border-sky-500">
                                <h5 class="mb-3 text-white text-17">Booking item</h5>
                                <p class="text-white/60">{{$field['booking_item']}}</p>
                            </div>
                        </div>
                        <div class="col-span-12 xl:col-span-4">
                            <div class="card p-5 rounded bg-sky-500 border-sky-500">
                                <h5 class="mb-3 text-white text-17">Booking id</h5>
                                <p class="text-white/60">{{$field['booking_id']}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-between">
                        <div class="col-span-12 xl:col-span-3">
                            <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                <div class="p-5 border-b border-sky-500">
                                    <h5 class="text-sky-500 text-17">Price</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-gray-700 mt-2 dark:text-zinc-100">
                                        @php
                                            $field = json_decode($reservation->reservation_contains, true);
                                            $field = $field['price'];
                                            $tooltip = '<ul class="!list-disc ml-6">';
                                            if (is_array($field)) {
                                                foreach ($field as $key => $value) {
                                                    if($key != 'booking_item'){
                                                        if (is_array($value)) {
                                                        $tooltip .= "<li><strong>$key:</strong> <ul class='!list-disc ml-6'>";
                                                        foreach ($value as $key2 => $value2) {
                                                            $tooltip .= "<li><strong>$key2:</strong> $value2</li>";
                                                        }
                                                        $tooltip .= '</ul></li>';
                                                        } else if ($key !== 'hotel_images') $tooltip .= "<li><strong>$key:</strong> $value</li>";
                                                    }
                                                    
                                                }
                                            }
                                            $tooltip .= '</ul>';
                                            echo $tooltip;
                                        @endphp
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xl:col-span-3">
                            <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                <div class="p-5 border-b border-sky-500">
                                    <h5 class="text-sky-500 text-17">Reservation Contains</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-gray-700 mt-2 dark:text-zinc-100">
                                        @php
                                            $field = json_decode($reservation->reservation_contains, true);
                                            $tooltip = '<ul class="!list-disc ml-6">';
                                            if (is_array($field)) {
                                                foreach ($field as $key => $value) {
                                                    if($key != 'price'){
                                                        if (is_array($value)) {
                                                        $tooltip .= "<li><strong>$key:</strong> <ul class='!list-disc ml-6'>";
                                                        foreach ($value as $key2 => $value2) {
                                                            $tooltip .= "<li><strong>$key2:</strong> $value2</li>";
                                                        }
                                                        $tooltip .= '</ul></li>';
                                                        } else if ($key !== 'hotel_images') $tooltip .= "<li><strong>$key:</strong> $value</li>";
                                                    }
                                                }
                                            }
                                            $tooltip .= '</ul>';
                                            echo $tooltip;
                                        @endphp
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xl:col-span-4">
                            <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                <div class="p-5 border-b border-sky-500">
                                    <h5 class="text-sky-500 text-17">Info</h5>
                                </div>
                                <div class="card-body">
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
                    </div>
                    <div class="row">
                        <div class="col-span-12 xl:col-span-12 w-100 mt-10">
                            <div class="card border-sky-500 dark:bg-zinc-800 h-100">
                                <div class="p-5 border-b border-sky-500">
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
