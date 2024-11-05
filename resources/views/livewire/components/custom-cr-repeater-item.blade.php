@php
    $containers = $getChildComponentContainers();
    $collapseAllActionIsVisible = $isCollapsible() && $getAction($getCollapseAllActionName())->isVisible();
    $expandAllActionIsVisible = $isCollapsible() && $getAction($getExpandAllActionName())->isVisible();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{}"
        {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-repeater grid gap-y-4']) }}
    >
        @if ($collapseAllActionIsVisible || $expandAllActionIsVisible)
            <div class="flex gap-x-3" :class="{ 'hidden': count($containers) < 2 }">
                @if ($collapseAllActionIsVisible)
                    <span x-on:click="$dispatch('repeater-collapse', '{{ $statePath }}')">
                        {{ $getAction($getCollapseAllActionName()) }}
                    </span>
                @endif
                @if ($expandAllActionIsVisible)
                    <span x-on:click="$dispatch('repeater-expand', '{{ $statePath }}')">
                        {{ $getAction($getExpandAllActionName()) }}
                    </span>
                @endif
            </div>
        @endif

        @if (!empty($containers))
            <ul>
                <x-filament::grid :default="$getGridColumns('default')" :sm="$getGridColumns('sm')" :md="$getGridColumns('md')"
                                  :lg="$getGridColumns('lg')" :xl="$getGridColumns('xl')" :two-xl="$getGridColumns('2xl')"
                                  :wire:end.stop="'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })'"
                                  x-sortable :data-sortable-animation-duration="$getReorderAnimationDuration()" class="items-start gap-4">
                    @foreach ($containers as $uuid => $item)
                        <li wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                            class="flex items-center gap-x-3 custom-repeater-class">
                            <div class="flex-grow">{{ $item }}</div>
                            @if ($isDeletable() && $getAction($getDeleteActionName())->isVisible())
                                <div>{{ $getAction($getDeleteActionName())(['item' => $uuid]) }}</div>
                            @endif
                        </li>

                    @endforeach
                </x-filament::grid>
            </ul>
        @endif

        @if ($isAddable() && $getAction($getAddActionName())->isVisible())
            <div class="flex justify-center">{{ $getAction($getAddActionName()) }}</div>
        @endif
    </div>
</x-dynamic-component>
