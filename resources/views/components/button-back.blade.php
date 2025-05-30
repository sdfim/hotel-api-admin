@props(['route', 'text', 'style'])
<a {{ $attributes->merge(['class' => 'btn btn-back', 'href' => $route]) }}>
    {{ $text }}
</a>
