@extends('layouts.master')
@section('title')
    {{ __('General Configuration') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="General" pagetitle="index"/>

    <div x-data="{ activeTab: 'general' }">
        <div class="mb-6 flex space-x-4 card p-5">
            <button
                @click="activeTab = 'general'"
                :class="{ 'bg-primary-600 text-white': activeTab === 'general', 'bg-gray-100 text-gray-700 hover:bg-gray-300': activeTab !== 'general' }"
                class="py-2 px-4 rounded-md font-medium text-sm transition-all duration-200 mr-4"
            >
                General Settings
            </button>
            <button
                @click="activeTab = 'scheduled-tasks'"
                :class="{ 'bg-primary-600 text-white': activeTab === 'scheduled-tasks', 'bg-gray-100 text-gray-700 hover:bg-gray-300': activeTab !== 'scheduled-tasks' }"
                class="py-2 px-4 rounded-md font-medium text-sm transition-all duration-200"
            >
                Scheduled Tasks
            </button>
        </div>

        <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body">
                    <div class="mt-6">
                        <!-- General Settings Tab -->
                        <div class="grid grid-cols-12 gap-5">
                            <div class="col-span-12">
                                <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                                    @livewire('general-configuration.create-general-configuration-form', compact('general_configuration'))
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Tasks Tab -->
        <div x-show="activeTab === 'scheduled-tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12">
                    <livewire:scheduled-tasks-table />
                </div>
            </div>
        </div>
    </div>
@endsection
