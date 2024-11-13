@php
    $tabs = [
        ['title' => 'Rooms', 'component' => 'hotels.hotel-room-table'],
        ['title' => 'Pricing Rules', 'component' => 'pricing-rules.pricing-rules-table'],
        ['title' => 'Key & Owner', 'component' => 'hotels.key-mapping-table'],
        ['title' => 'Deposit Information', 'component' => 'hotels.hotel-deposit-information-table'],
        ['title' => 'Website Search Generation', 'component' => 'hotels.hotel-web-finder-table'],
        ['title' => 'Contact Information', 'component' => 'hotels.hotel-contact-information-table'],
        ['title' => 'Promotions', 'component' => 'hotels.hotel-promotion-table'],
        ['title' => 'Attributes', 'component' => 'hotels.hotel-attributes-table'],
        ['title' => 'Informational Service', 'component' => 'hotels.hotel-informative-services-table'],
        ['title' => 'Age Restrictions', 'component' => 'hotels.hotel-age-restriction-table'],
        ['title' => 'Affiliations', 'component' => 'hotels.hotel-affiliations-table'],
        ['title' => 'Fee and Tax', 'component' => 'hotels.hotel-fee-tax-table'],
        ['title' => 'Descriptive Content Section', 'component' => 'hotels.hotel-descriptive-content-section-table'],
    ];
@endphp

<div class="mt-8 mb-8">
    <div x-data="{ activeTab: '{{ Str::slug($tabs[0]['title']) }}' }">
        <ul class="flex border-b filament-tabs filament-tabs-item">
            @foreach ($tabs as $tab)
                <li class="mr-1 flex items-end">
                    <a :class="{
                            'border-b-2 border-primary-500 text-primary-600 font-semibold': activeTab === '{{ Str::slug($tab['title']) }}',
                            'text-gray-500 hover:text-primary-500': activeTab !== '{{ Str::slug($tab['title']) }}'
                        }"
                       class="inline-block py-3 px-5 cursor-pointer transition duration-200 ease-in-out"
                       @click="activeTab = '{{ Str::slug($tab['title']) }}'">
                        <span>{{ $tab['title'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="w-full pt-4">
            @foreach ($tabs as $tab)
                <div x-show="activeTab === '{{ Str::slug($tab['title']) }}'" class="filament-tabs-panel">
                    @livewire($tab['component'], ['hotelId' => $hotelId])
                </div>
            @endforeach
        </div>
    </div>
</div>


