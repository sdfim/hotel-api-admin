<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'btn py-2 px-10 font-bold text-white bg-maintheme-900 border-maintheme-900
            hover:text-maintheme-900 hover:bg-maintheme-200 hover:border-maintheme-700
            focus:text-maintheme-900 focus:bg-maintheme-700 focus:border-maintheme-700 focus:ring focus:ring-maintheme-500/30
            active:text-maintheme-900 active:bg-maintheme-700 active:border-maintheme-700
            dark:border-transparent']) }}>
    {{ $slot }}
</button>
