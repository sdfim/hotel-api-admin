<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'btn py-2 px-10 font-bold text-white bg-mandarin-900 border-mandarin-900
            hover:text-mandarin-900 hover:bg-mandarin-200 hover:border-mandarin-700
            focus:text-mandarin-900 focus:bg-mandarin-700 focus:border-mandarin-700 focus:ring focus:ring-mandarin-500/30
            active:text-mandarin-900 active:bg-mandarin-700 active:border-mandarin-700
            dark:border-transparent']) }}>
    {{ $slot }}
</button>
