@extends('layouts.master')
@section('title')
    {{ __('Pricing Rule Details') }}
@endsection
@section('content')
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100 " x-data="{ message: '{{ $text['show'] }}' }"
                    x-text="message"></h6>
            </div>
            <div class="card-body text-slate-900 dark:text-white text-base font-medium tracking-tight">
                <div class="relative overflow-x-auto">
                    <div class="row">
                        <div class="col-lg-12 margin-tb">
                            <div class="mb-6">
                                <x-button-back route="{{ route('pricing-rules.index') }}" text="Back"/>
                            </div>
                        </div>
                    </div>
                    <div class="mt-10 sm:mt-0">
                        <strong>Name:</strong>
                        {{ $pricingRule->name }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Manipulable price type:</strong>
                        {{ $pricingRule->manipulable_price_type }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Price value type:</strong>
                        {{ $pricingRule->price_value_type }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Price value:</strong>
                        {{ $pricingRule->price_value }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Price value target:</strong>
                        {{ $pricingRule->price_value_target }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Rule start date:</strong>
                        {{ $pricingRule->rule_start_date->toDateString() }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Rule expiration date:</strong>
                        {{ $pricingRule->rule_expiration_date->toDateString() }}
                    </div>
                    <h5 class="mt-10">Conditions</h5>
                    <ul class="!list-disc ml-6">
                        @foreach($pricingRule->conditions as $condition)
                            <li>
                                {{ ucfirst(str_replace('_', ' ', $condition->field)) }} {{ $condition->compare }} {{ $condition->value_from }}
                                @if ($condition->compare === 'between')
                                    and {{ $condition->value_to }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Created at:</strong>
                        {{ $pricingRule->created_at->toDateString() }}
                    </div>
                    <x-section-border/>
                    <div class="mt-10 sm:mt-0">
                        <strong>Update at:</strong>
                        {{ $pricingRule->updated_at->toDateString() }}
                    </div>
                    <x-section-border/>
                </div>

            </div>
        </div>
    </div>
@endsection
