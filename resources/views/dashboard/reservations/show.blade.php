@extends('layouts.master')
@section('title')
    {{ __('Reservation Details') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}"/>
    <style>
        :root {
            --luxury-gold: #C29C75;
            --luxury-dark: #222222;
            --luxury-light: #F8F8F8;
        }
        .luxury-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .luxury-header {
            font-family: 'Playfair Display', serif; /* Assuming standard serif fallback if not loaded */
            letter-spacing: 0.05em;
            color: var(--luxury-dark);
            border-bottom: 2px solid var(--luxury-gold);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .luxury-label {
            color: #6b7280;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-weight: 500;
        }
        .luxury-value {
            color: var(--luxury-dark);
            font-weight: 600;
        }
        .luxury-badge {
            background-color: var(--luxury-gold);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        .section-title {
            background-color: var(--luxury-dark) !important;
            color: white !important;
            border-left: 4px solid var(--luxury-gold);
        }
        .luxury-tag {
            background-color: var(--luxury-light);
            color: var(--luxury-dark);
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .luxury-tag-gold {
            background-color: #f3e9df;
            color: #8b6e4e;
            border-color: #e9dccb;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
@endsection
@section('content')
    <div class="col-span-12 text-gray-700 dark:text-gray-100">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600 ">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-18 font-serif uppercase tracking-widest text-[#C29C75]" x-data="{ message: '{{ $text['show'] }}' }" x-text="message"></h6>
            </div>
            @php
                $field = json_decode($reservation->reservation_contains, true);
                $total_net = Arr::get($field, 'price.total_net', 0);
                $total_price = Arr::get($field, 'price.total_price', 0);
                $markup = $total_price - $total_net;
                $passengers_by_room = \App\Repositories\ApiBookingInspectorRepository::getPassengersByRoom($reservation->booking_id, $reservation->booking_item);
                [$special_requests_by_room, $comments_by_room] = \App\Repositories\ApiBookingInspectorRepository::getSpecialRequestsAndComments($reservation->booking_id, $reservation->booking_item) ?? [];
            @endphp
            <div class="card-body">
                <!-- Back Button -->
                <div class="mb-3 row">
                    <x-button-back route="{{ route('reservations.index') }}" text="Back"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Type and Supplier Column -->
                    <div class="luxury-card p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="luxury-label">Type</p>
                                <p class="luxury-value font-mono text-xs">{{ $field['type'] }}</p>
                            </div>
                            <div>
                                <p class="luxury-label">Supplier</p>
                                <p class="luxury-value font-mono text-xs">{{ $field['supplier'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Search id, Booking item, Booking id Column -->
                    <div class="luxury-card p-4 col-span-1 md:col-span-2 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="luxury-label">Search ID</p>
                                <p class="luxury-value font-mono text-xs">{{ $field['search_id'] }}</p>
                            </div>
                            <div>
                                <p class="luxury-label">Booking Item</p>
                                <p class="luxury-value font-mono text-xs">{{ $field['booking_item'] }}</p>
                            </div>
                            <div>
                                <p class="luxury-label">Booking ID</p>
                                <p class="luxury-value font-mono text-xs">{{ $field['booking_id'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                    <!-- Price Column -->
                    <div class="luxury-card p-0 overflow-hidden border-0 shadow-sm bg-white">
                        <div class="section-title p-3 px-4">
                            <h5 class="text-15 text-white mb-0 font-serif tracking-wider uppercase">Price Details</h5>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                                <div>
                                    <p class="luxury-label mb-1">Currency</p>
                                    <p class="luxury-value">{{ Arr::get($field, 'price.currency', 'USD') }}</p>
                                </div>
                                <div>
                                    <p class="luxury-label mb-1">Total Tax</p>
                                    <p class="luxury-value text-gray-700">{{ Arr::get($field, 'price.total_tax', 0) }}</p>
                                </div>
                                <div>
                                    <p class="luxury-label mb-1">Total Net</p>
                                    <p class="luxury-value text-gray-700">{{ $total_net }}</p>
                                </div>
                                <div>
                                    <p class="luxury-label mb-1">Total Fees</p>
                                    <p class="luxury-value text-gray-700">{{ Arr::get($field, 'price.total_fees', 0) }}</p>
                                </div>
                                <div>
                                    <p class="luxury-label mb-1">Markup</p>
                                    <p class="luxury-value text-gray-700">{{ $markup }}</p>
                                </div>
                                <div class="col-span-2 pt-3 border-t border-gray-100 mt-2 flex justify-between items-start">
                                    <div>
                                        <p class="luxury-label mb-1">Total Price</p>
                                        <p class="luxury-value text-4xl font-serif text-[#C29C75]">{{ $total_price }}</p>
                                        @if(isset($advisorCommission) && $advisorCommission > 0)
                                            <div class="mt-3">
                                                <p class="luxury-label text-xs mb-0">Advisor Commission</p>
                                                <p class="text-lg font-serif text-[#8b6e4e]">{{ number_format($advisorCommission, 2) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="luxury-label mb-1">Paid Status</p>
                                        <span class="text-2xl luxury-badge px-4 py-3 block">{{ $reservation->paid ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Information Column (Merged Info & Hotel) -->
                    <div class="luxury-card p-0 overflow-hidden border-0 shadow-sm bg-white lg:col-span-2">
                        <div class="section-title p-3 px-4">
                            <h5 class="text-15 text-white mb-0 font-serif tracking-wider uppercase">Reservation Information</h5>
                        </div>
                        <div class="p-6">
                            <!-- Full Width Hotel Name -->
                            <div class="mb-8 pb-6 border-gray-100">
                                <span class="luxury-label block mb-2">Hotel Name</span>
                                <h2 class="text-3xl font-serif text-[#C29C75] font-bold leading-tight uppercase tracking-tight">{{ $field['hotel_name'] }}</h2>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Info Section -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="luxury-label mb-1">Date Travel</p>
                                        <p class="luxury-value text-lg">{{ $reservation->date_travel }}</p>
                                    </div>
                                    <div>
                                        <p class="luxury-label mb-1">Passenger</p>
                                        <p class="luxury-value text-lg">{{ $reservation->passenger_surname }}</p>
                                    </div>
                                    <div>
                                        <p class="luxury-label mb-1">Channel</p>
                                        <p class="luxury-value">{{ $reservation->channel->name }}</p>
                                    </div>
                                </div>
                                <!-- Hotel Metadata Section -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="luxury-label mb-1">GIATA ID</p>
                                        <p class="luxury-value">{{ $field['hotel_id'] }}</p>
                                    </div>
                                    @if($reservation->apiBookingsMetadata?->hotel_supplier_id)
                                        <div>
                                            <p class="luxury-label mb-1">Supplier Hotel ID</p>
                                            <p class="luxury-value">{{ $reservation->apiBookingsMetadata->hotel_supplier_id }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="luxury-label mb-1">Advisor Email</p>
                                        <p class="luxury-value text-sm text-gray-500 italic">{{ $advisorEmail ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms Column -->
                    <div class="luxury-card p-0 overflow-hidden col-span-1 md:col-span-2 lg:col-span-3 mt-6">
                        <div class="section-title p-3 px-4">
                            <h5 class="text-15 text-white mb-0 font-serif tracking-wider uppercase">Rooms Information</h5>
                        </div>
                        <div class="p-5">

                        @php
                            $roomNames = array_filter(explode(';', $field['price']['supplier_room_name'] ?? ''));
                            $roomCodes = array_filter(explode(';', $field['price']['room_type'] ?? ''));
                            $rateCodes = explode(';', $field['price']['rate_plan_code'] ?? '');
                            $rateDescriptions = explode(';', $field['price']['rate_description'] ?? '');
                            $mealPlans = explode(';', $field['price']['meal_plan'] ?? '');
                            $collectedRoomImages = [];
                        @endphp

                        <div class="flex flex-col gap-6">
                            @foreach($roomNames as $index => $roomName)
                                @php
                                    $room = $reservation->apiBookingsMetadata->contentHotel?->rooms->where('name', trim($roomName))->first();
                                    $roomImages = $room ? $room->galleries->flatMap(fn($g) => $g->images)->pluck('image_url')->toArray() : [];
                                    if ($roomImages) {
                                        $collectedRoomImages = array_merge($collectedRoomImages, $roomImages);
                                    }
                                @endphp

                                <div class="p-6 luxury-card bg-white border-l-4 border-l-[#C29C75] shadow-sm">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                        <!-- Column 1: Core Room Info -->
                                        <div>
                                            <h4 class="luxury-label mb-1">Room {{ $index + 1 }}</h4>
                                            <h3 class="text-xl font-serif text-[#222222] font-semibold mb-4">{{ trim($roomName) }}</h3>

                                            @if(count($roomNames) > 1 && isset($roomPrices[$index]))
                                                <div class="mb-5 p-3 bg-[#fdfaf7] border border-[#f3e9df] rounded-lg inline-block min-w-[150px]">
                                                    <p class="luxury-label text-[10px] uppercase tracking-wider mb-1">Room Price</p>
                                                    <p class="text-xl font-serif text-[#C29C75] font-bold">
                                                        {{ Arr::get($field, 'price.currency', 'USD') }} {{ number_format($roomPrices[$index], 2) }}
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="space-y-4">
                                                <div>
                                                    <p class="luxury-label mb-1">Room Code</p>
                                                    <p class="luxury-value">{{ isset($roomCodes[$index]) ? trim($roomCodes[$index]) : 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <p class="luxury-label mb-1">Rate Code</p>
                                                    <p class="luxury-value">{{ isset($rateCodes[$index]) ? trim($rateCodes[$index]) : 'N/A' }}</p>
                                                </div>
                                                @if(!empty(trim($mealPlans[$index] ?? '')))
                                                    <div>
                                                        <p class="luxury-label mb-1">Meal Plan</p>
                                                        <p class="luxury-value text-[#C29C75]">{{ trim($mealPlans[$index]) }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($room)
                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    @if($room->area)
                                                        <span class="luxury-tag" title="Area">
                                                            <i class="bx bx-area"></i> {{ $room->area }} sq ft
                                                        </span>
                                                    @endif
                                                    @if($room->max_occupancy)
                                                        <span class="luxury-tag" title="Max Occupancy">
                                                            <i class="bx bx-group"></i> {{ $room->max_occupancy }} Guests
                                                        </span>
                                                    @endif
                                                    @if($room->room_views)
                                                        @foreach($room->room_views as $view)
                                                            <span class="luxury-tag luxury-tag-gold">{{ $view }}</span>
                                                        @endforeach
                                                    @endif
                                                    @if($room->bed_groups)
                                                        @foreach($room->bed_groups as $bed)
                                                            <span class="luxury-tag luxury-tag-gold">
                                                                <i class="bx bx-bed"></i> {{ $bed['description'] ?? $bed }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    @foreach($room->attributes as $attr)
                                                        <span class="luxury-tag">{{ $attr->name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Column 2: Passengers -->
                                        <div>
                                            @if(isset($passengers_by_room[$index + 1]))
                                                <h5 class="luxury-label mb-3">Passengers</h5>
                                                <div class="space-y-2">
                                                    @foreach($passengers_by_room[$index + 1] as $passenger)
                                                        <div class="bg-gray-50 p-3 rounded text-sm border-l-2 border-gray-200 mb-1 flex justify-between items-center">
                                                             <div>
                                                                 <p class="font-semibold text-gray-800">{{ ucfirst($passenger['title'] ?? '') }} {{ $passenger['given_name'] ?? '' }} {{ $passenger['family_name'] ?? '' }}</p>
                                                                 <p class="text-xs text-gray-500 mt-1">Age: {{ $passenger['age'] ?? 'N/A' }} | DOB: {{ $passenger['date_of_birth'] ?? 'N/A' }}</p>
                                                             </div>
                                                             @if(isset($passenger['age']) && is_numeric($passenger['age']) && $passenger['age'] < 18)
                                                                 <div class="text-[#C29C75] text-xl" title="Child">
                                                                     <i class="bx bxs-face"></i>
                                                                 </div>
                                                             @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="luxury-label mb-3">Passengers</p>
                                                <p class="text-sm text-gray-400 italic">No passengers found</p>
                                            @endif
                                        </div>

                                        <!-- Column 3: Requests & Images -->
                                        <div>
                                             @if(!empty(trim($special_requests_by_room[$index + 1] ?? '')) || !empty(trim($comments_by_room[$index + 1] ?? '')))
                                                <h5 class="luxury-label mb-3">Requests & Notes</h5>
                                                @if(!empty(trim($special_requests_by_room[$index + 1] ?? '')))
                                                     <p class="text-xs luxury-label">Special Request</p>
                                                     <p class="text-sm text-gray-700 italic mb-3">{{ $special_requests_by_room[$index + 1] }}</p>
                                                @endif
                                                @if(!empty(trim($comments_by_room[$index + 1] ?? '')))
                                                     <p class="text-xs luxury-label">Comment</p>
                                                     <p class="text-sm text-gray-700 italic mb-4">{{ $comments_by_room[$index + 1] }}</p>
                                                @endif
                                             @endif

                                             @if ($roomImages)
                                                <h5 class="luxury-label mb-3">Room Gallery</h5>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($roomImages as $imageNumber => $image)
                                                        @php
                                                            $imageUrl = Str::startsWith($image, ['http://', 'https://']) ? $image : Storage::url($image);
                                                        @endphp
                                                        <a href="{{ $imageUrl }}" class="reservation-show-glightbox">
                                                            <img class="w-32 h-32 md:w-40 md:h-40  rounded object-cover cursor-pointer hover:opacity-80 transition shadow-sm border border-gray-100"
                                                                 src="{{ $imageUrl }}" alt="Room {{ $index + 1 }} - Image {{ $imageNumber }}">
                                                        </a>
                                                    @endforeach
                                                </div>
                                             @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                </div> <!-- Close grid-cols-3 -->

                <!-- Hotel Images Column -->
                <div class="grid grid-cols-1 mt-8">
                    <div class="luxury-card p-0 overflow-hidden">
                        <div class="section-title p-3 px-4">
                            <h5 class="text-15 text-white mb-0 font-serif tracking-wider uppercase">Hotel Gallery & Experience</h5>
                        </div>
                        <div class="p-6">
                            @php
                                $record = json_decode($reservation->reservation_contains);
                                $images = [];
                                if(isset($record->hotel_images)){
                                    $images = json_decode($record->hotel_images);
                                }

                                if (!empty($collectedRoomImages)) {
                                    $images = array_unique(array_merge($images, $collectedRoomImages));
                                }

                                if (empty($images)) {
                                    echo 'Nothing to show';
                                }
                            @endphp

                            <div class="flex flex-wrap gap-3 overflow-x-auto pb-2">
                                @if ($images)
                                    @foreach($images as $imageNumber => $image)
                                        @php
                                            $imageUrl = Str::startsWith($image, ['http://', 'https://']) ? $image : Storage::url($image);
                                        @endphp
                                        <a href="{{ $imageUrl }}" class="group relative flex-shrink-0 reservation-show-glightbox">
                                            <img class="w-32 h-32 md:w-40 md:h-40 rounded-lg object-cover cursor-pointer transition-transform duration-300 group-hover:scale-105 shadow-md border border-gray-100"
                                                 src="{{ $imageUrl }}" alt="Image {{ $imageNumber }}">
                                            <div class="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors rounded-lg"></div>
                                        </a>
                                    @endforeach
                                @endif
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
