@php use Modules\HotelContentRepository\Models\Hotel; @endphp
@extends('layouts.master')
@section('title')
    {{ __('Create Pricing Rule') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100" x-data="{ message: '{{ $text['create'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mb-6 row">
                                @if ($isSrCreator && $giataCodeProperty)
                                    @php
                                        $hotel = Hotel::where('giata_code', $giataCodeProperty)->first();
                                    @endphp
                                    @if ($hotel)
                                        <x-button-back class="ml-4" route="{{ route('hotel-repository.edit', $hotel->id) }}" text="Back"/>
                                    @endif
                                @else
                                    <x-button-back class="pr-6" route="{{ route('pricing-rules.index') }}" text="Back"/>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mx-1 py-1 col-span-9 xl:col-span-6">
                        @livewire('pricing-rules.create-pricing-rule', ['isSrCreator' => $isSrCreator, 'giataCodeProperty' => $giataCodeProperty])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
