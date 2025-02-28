@props(['route', 'text', 'style'])
<a
    {{ $attributes->merge([
        'class' => 'btn py-2 px-10 font-bold text-violet-900 bg-violet-50 border-violet-50
            hover:text-white hover:bg-violet-600 hover:border-violet-600
            focus:text-white focus:bg-violet-600 focus:border-violet-600 focus:ring focus:ring-violet-500/30
            active:text-white active:bg-violet-600 active:border-violet-600
            dark:border-transparent',
        'href' => $route]) }}>
    {{ $text }}
</a>
