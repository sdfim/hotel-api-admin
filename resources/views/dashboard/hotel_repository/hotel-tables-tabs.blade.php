@php
    $tabGroups = [
        'Additional Info' => [
            ['title' => 'Attributes', 'component' => 'hotels.hotel-attributes-table'],
            ['title' => 'Age Restrictions', 'component' => 'hotels.hotel-age-restriction-table'],
            ['title' => 'Affiliations', 'component' => 'hotels.hotel-affiliations-table'],
            ['title' => 'Contact Information', 'component' => 'hotels.hotel-contact-information-table'],
        ],
        'Informational Service' => [
            ['title' => 'Informational Service', 'component' => 'hotels.hotel-informative-services-table'],
        ],
        'Website' => [
            ['title' => 'Website Search Generation', 'component' => 'hotels.hotel-web-finder-table'],
        ],
        'Rooms' => [
            ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
        ],
        'Promotions' => [
            ['title' => 'Promotions', 'component' => 'hotels.hotel-promotion-table'],
        ],
        'Pricing Rules' => [
            ['title' => 'Key & Owner', 'component' => 'hotels.key-mapping-table'],
            ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
            ['title' => 'Deposit Information', 'component' => 'hotels.hotel-deposit-information-table'],
        ],
        'Fee and Tax' => [
            ['title' => 'Fee and Tax', 'component' => 'hotels.hotel-fee-tax-table'],
        ],
        'Descriptive Content' => [
            ['title' => 'Descriptive Content Section', 'component' => 'hotels.hotel-descriptive-content-section-table'],
        ],
    ];

    ksort($tabGroups);

@endphp

<div x-data="{ activeTab: '{{ Str::slug(array_key_first($tabGroups)) }}' }" class="sr_tab-container">
    <ul class="sr_tab-list flex justify-center">
        @foreach ($tabGroups as $group => $tabs)
            <li class="sr_tab-item mr-1 flex items-end">
                <a href="#"
                   class="sr_tab-link"
                   :class="{ 'sr_active': activeTab === '{{ Str::slug($group) }}' }"
                   @click.prevent="activeTab = '{{ Str::slug($group) }}'">
                    <span>{{ $group }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    <div class="sr_tab-content w-full">
        @foreach ($tabGroups as $group => $tabs)
            <div x-show="activeTab === '{{ Str::slug($group) }}'" class="sr_tab-panel">
                @if ($group === 'Additional Info')
                    <div class="grid grid-cols-2 gap-6">
                        @foreach ($tabs as $tab)
                            <div>
                                <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                                @livewire($tab['component'], ['hotelId' => $hotelId])
                            </div>
                        @endforeach
                    </div>
                @else
                    @foreach ($tabs as $tab)
                        <h3 class="sr_tab-title text-lg font-semibold mb-4 mt-4">{{ $tab['title'] }}</h3>
                        @if ($tab['title'] === 'Pricing Rules')
                            @livewire($tab['component'], ['hotelId' => $hotelId, 'isSrCreator' => true])
                        @else
                            @livewire($tab['component'], ['hotelId' => $hotelId])
                        @endif
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>
