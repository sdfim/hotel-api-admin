@php
    $tabGroups = [
        'Additional Info' => [
            ['title' => 'Key & Owner', 'component' => 'hotels.key-mapping-table'],
            ['title' => 'Deposit Information', 'component' => 'hotels.hotel-deposit-information-table'],
            ['title' => 'Attributes', 'component' => 'hotels.hotel-attributes-table'],
            ['title' => 'Informational Service', 'component' => 'hotels.hotel-informative-services-table'],
            ['title' => 'Age Restrictions', 'component' => 'hotels.hotel-age-restriction-table'],
            ['title' => 'Affiliations', 'component' => 'hotels.hotel-affiliations-table'],
        ],
        'Search' => [
            ['title' => 'Website Search Generation', 'component' => 'hotels.hotel-web-finder-table'],
            ['title' => 'Contact Information', 'component' => 'hotels.hotel-contact-information-table'],
        ],
        'Rooms' => [
            ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
        ],
        'Promotions' => [
            ['title' => 'Promotions', 'component' => 'hotels.hotel-promotion-table'],
        ],
        'Pricing Rules' => [
//            ['title' => 'Pricing Rules', 'component' => 'hotels.hotel-pricing-rules-table'],
        ],
        'Fee and Tax' => [
            ['title' => 'Fee and Tax', 'component' => 'hotels.hotel-fee-tax-table'],
        ],
        'Descriptive Content' => [
            ['title' => 'Descriptive Content Section', 'component' => 'hotels.hotel-descriptive-content-section-table'],
        ],
    ];
@endphp

<div class="mt-8 mb-8">
    <div x-data="{ activeTab: '{{ Str::slug(array_keys($tabGroups)[0]) }}' }">
        <ul class="flex border-b filament-tabs filament-tabs-item justify-center">
            @foreach ($tabGroups as $group => $tabs)
                <li class="mr-1 flex items-end">
                    <a :class="{
                            'border-b-2 border-primary-500 text-primary-600 font-semibold text-lg': activeTab === '{{ Str::slug($group) }}',
                            'text-gray-500 hover:text-primary-500 text-lg': activeTab !== '{{ Str::slug($group) }}'
                        }"
                       class="inline-block py-3 px-5 cursor-pointer transition duration-200 ease-in-out text-center w-full"
                       @click="activeTab = '{{ Str::slug($group) }}'">
                        <span>{{ $group }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="w-full pt-4">
            @foreach ($tabGroups as $group => $tabs)
                <div x-show="activeTab === '{{ Str::slug($group) }}'" class="filament-tabs-panel">
                    @foreach ($tabs as $tab)
                        <h3 class="text-lg font-semibold">{{ $tab['title'] }}</h3>
                        @livewire($tab['component'], ['hotelId' => $hotelId])
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
