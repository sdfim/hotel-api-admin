@props(['route', 'iconClass'])
<a
    {{ $attributes->merge(['class' => 'btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-maintheme-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-maintheme-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100', 'href' => $route]) }}>
    <i class="{{$iconClass}} block text-lg"></i>
</a>
