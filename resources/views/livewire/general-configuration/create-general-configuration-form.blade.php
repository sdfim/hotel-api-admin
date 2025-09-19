@php use App\Models\GeneralConfiguration; @endphp

<div>
    @canany(['update', 'create'], GeneralConfiguration::class)
        <div class="flex justify-end mb-8 space-x-2">
            <x-button class="py-1 px-3 text-sm" wire:click="clearCache" style="background-color: var(--theme-color); height: 33px;">
                {{ __('Clear Search Cache') }}
            </x-button>
            <x-button class="py-1 px-3 text-sm" wire:click="clearAllCache" style="background-color: var(--theme-color); height: 33px;">
                {{ __('Clear All Cache') }}
            </x-button>
            <x-button class="py-1 px-3 text-sm" wire:click="clearMappingCache" style="background-color: var(--theme-color); height: 33px;">
                {{ __('Clear Mapping Cache') }}
            </x-button>
        </div>
    @endcan

    <form wire:submit="save">
        {{ $this->form }}

        @if ($create)
            @can('update', GeneralConfiguration::class)
                <x-button class="mt-4">
                    {{ __('Update') }}
                </x-button>
            @else
                <x-button class="mt-4" disabled>
                    {{ __('Update') }}
                </x-button>
            @endcan
        @else
            @can('create', GeneralConfiguration::class)
                <x-button class="mt-4">
                    {{ __('Create') }}
                </x-button>
            @else
                <x-button class="mt-4" disabled>
                    {{ __('Create') }}
                </x-button>
            @endcan
        @endif
    </form>

    <x-filament-actions::modals/>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            var inputElement = document.querySelector('.choices__input--cloned');
            inputElement.style.minWidth = '37ch';
        }, 500);
    });
</script>
