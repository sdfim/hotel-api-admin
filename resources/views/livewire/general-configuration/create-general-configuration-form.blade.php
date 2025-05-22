@php use App\Models\GeneralConfiguration; @endphp

<div>
    @canany(['update', 'create'], GeneralConfiguration::class)
        <x-button class="mt-4 mb-8" wire:click="clearCache" style="background-color: rgb(81, 86, 190);">
            {{ __('Clear Search Cache') }}
        </x-button>
        <x-button class="mt-4 mb-8" wire:click="clearMappingCache" style="background-color: rgb(120, 86, 190);">
            {{ __('Clear Mapping Cache') }}
        </x-button>
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

