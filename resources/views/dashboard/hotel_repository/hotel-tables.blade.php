<div class="mt-8">
    <div class="flex flex-col xl:flex-row gap-8">
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Key & Owner</h2>
            @livewire('hotels.key-mapping-table', ['hotelId' => $hotelId])
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Deposit Information</h2>
            @livewire('hotels.hotel-deposit-information-table', ['hotelId' => $hotelId])
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="flex flex-col xl:flex-row gap-8">
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Website Search Generation</h2>
            @livewire('hotels.hotel-web-finder-table', ['hotelId' => $hotelId])
        </div>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold">Contact Information</h2>
    @livewire('hotels.hotel-contact-information-table', ['hotelId' => $hotelId])
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold">Rooms</h2>
    @livewire('hotels.hotel-room-table', ['hotelId' => $hotelId])
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold">Promotions</h2>
    @livewire('hotels.hotel-promotion-table', ['hotelId' => $hotelId])
</div>

<div class="mt-8">
    <div class="flex flex-col xl:flex-row gap-8">
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Attributes</h2>
            @livewire('hotels.hotel-attributes-table', ['hotelId' => $hotelId])
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Informational Service</h2>
            @livewire('hotels.hotel-informative-services-table', ['hotelId' => $hotelId])
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="flex flex-col xl:flex-row gap-8">
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Age Restrictions</h2>
            @livewire('hotels.hotel-age-restriction-table', ['hotelId' => $hotelId])
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Affiliations</h2>
            @livewire('hotels.hotel-affiliations-table', ['hotelId' => $hotelId])
        </div>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold">Fee and Tax</h2>
    @livewire('hotels.hotel-fee-tax-table', ['hotelId' => $hotelId])
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold">Descriptive Content Section</h2>
    @livewire('hotels.hotel-descriptive-content-section-table', ['hotelId' => $hotelId])
</div>
