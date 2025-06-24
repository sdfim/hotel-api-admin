@extends('layouts.master')
@section('title')
    {{ __('General Configuration') }}
@endsection
@section('content')
    <!-- -->
    <x-page-title title="General Configuration" pagetitle="index"/>

    <div class="card dark:bg-zinc-800 dark:border-zinc-600">
        <div class="card-body">
            <div x-data="{ activeTab: 'general' }">
                <div class="border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'general'"
                            :class="{ 'border-primary-500 text-primary-600': activeTab === 'general', 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'general' }"
                            class="py-4 px-1 border-b-2 font-medium text-sm">
                            General Settings
                        </button>
                        <button @click="activeTab = 'scheduled-tasks'"
                            :class="{ 'border-primary-500 text-primary-600': activeTab === 'scheduled-tasks', 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': activeTab !== 'scheduled-tasks' }"
                            class="py-4 px-1 border-b-2 font-medium text-sm">
                            Scheduled Tasks
                        </button>
                    </nav>
                </div>

                <div class="mt-6">
                    <!-- General Settings Tab -->
                    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="grid grid-cols-12 gap-5">
                            <div class="col-span-12">
                                <div class="ml-1 mr-1 col-span-9 xl:col-span-6">
                                    @livewire('general-configuration.create-general-configuration-form', compact('general_configuration'))
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
            </div>
        </div>
    </div>
@endsection
