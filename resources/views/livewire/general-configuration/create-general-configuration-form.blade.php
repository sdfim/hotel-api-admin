<div>
    <x-button class="mt-4 mb-8" wire:click="clearCache" style="background-color: rgb(81, 86, 190);">
        {{ __('Clear Search Cache') }}
    </x-button>

    <form wire:submit="save">
        {{ $this->form }}

        @if ($create)
            <x-button class="mt-4">
                {{ __('Update') }}
            </x-button>
        @else
            <x-button class="mt-4">
                {{ __('Create') }}
            </x-button>
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

