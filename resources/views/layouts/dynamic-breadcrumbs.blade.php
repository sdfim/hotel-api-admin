@php
    $segments = request()->segments();
    if (count($segments) > 0) {
        $breadcrumbs = [];
        $url = '';

        $segmentMap = [
            'admin' => 'Admin',
            'hotel-repository' => 'Hotels',
            'product-repository' => 'Products',
            'vendor-repository' => 'Vendors',
            'image-galleries' => 'Galleries',
            'reservations' => 'Reservations',
            'pricing-rules' => 'Pricing Rules',
            'property-weighting' => 'Property Weighting',
            'search-inspector' => 'Search Inspector',
            'booking-inspector' => 'Booking Inspector',
            'payment-inspector' => 'Payment Intents',
            'exceptions-report' => 'Exceptions',
            'images' => 'Images',
            'expedia' => 'Expedia',
            'ice-portal' => 'Ice Portal',
            'oracle' => 'Oracle',
            'hotel-trader' => 'Hotel Trader',
            'hbsi-property' => 'HBSI',
            'hilton' => 'Hilton',
            'properties' => 'Giata Properties',
            'geography' => 'Geography',
            'users' => 'Users',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'general_configuration' => 'General',
            'channels' => 'Channels',
            'suppliers' => 'Suppliers',
            'configurations' => 'Configurations',
            'attributes' => 'Attributes',
            'attribute-categories' => 'Categories',
            'amenities' => 'Amenities',
            'descriptive-types' => 'Descriptive Types',
            'job-descriptions' => 'Departments',
        ];

        // Custom Hierarchy Logic
        $finalSegments = [];
        foreach ($segments as $segment) {
            if (is_numeric($segment))
                continue;

            // If we are at 'hotel-repository' and it's NOT coming after 'product-repository' already
            if ($segment === 'hotel-repository' && !in_array('product-repository', array_column($finalSegments, 'original'))) {
                $finalSegments[] = [
                    'original' => 'product-repository',
                    'name' => 'Products',
                    'url' => url('/admin/product-repository')
                ];
            }

            $url .= '/' . $segment;
            $slug = strtolower($segment);
            $name = $segmentMap[$slug] ?? ucwords(str_replace(['-', '_'], ' ', $segment));

            $finalSegments[] = [
                'original' => $segment,
                'name' => $name,
                'url' => url($url)
            ];
        }
    }
@endphp

@if(count($finalSegments ?? []) > 0)
    <div class="breadcrumb-container {{ $class ?? '' }}">
        <nav aria-label="breadcrumb">
            <ol class="flex items-center text-[13px] mb-1">
                @foreach ($finalSegments as $fSegment)
                    <li class="flex items-center">
                        @if ($loop->last)
                            <span class="breadcrumb-current">{{ $fSegment['name'] }}</span>
                        @else
                            <a href="{{ $fSegment['url'] }}" class="breadcrumb-link">
                                {{ $fSegment['name'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
@endif