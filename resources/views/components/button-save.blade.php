<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'btn py-2 px-10 font-bold text-white bg-violet-900 border-violet-900
            hover:text-violet-900 hover:bg-violet-200 hover:border-violet-700
            focus:text-violet-900 focus:bg-violet-700 focus:border-violet-700 focus:ring focus:ring-violet-500/30
            active:text-violet-900 active:bg-violet-700 active:border-violet-700
            dark:border-transparent']) }}>
    {{ $slot }}
</button>
