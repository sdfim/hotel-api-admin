<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', env('APP_NAME'))</title>

    {{-- Web Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;500&display=swap"
        rel="stylesheet">

    {{-- Tailwind CDN for quick layout --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        playfair: ['"Playfair Display"', 'serif'],
                        montserrat: ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        vidantaBlack: '#1C1B1B',
                        vidantaBronze: '#C29C75',
                        vidantaBeige: '#FBF9F6',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="bg-[#F7F7F7] min-h-screen flex flex-col items-center justify-center p-6">

    <div class="mb-12">
        <img src="{{ asset('images/firm-logo.png') }}" alt="{{ env('APP_NAME') }}" class="h-16">
    </div>

    <div class="bg-white max-w-2xl w-full shadow-xl overflow-hidden">
        <div class="h-1 bg-vidantaBronze w-full"></div>
        <div class="p-12 text-center">
            @yield('content')
        </div>
    </div>

    <div class="mt-8 text-vidantaBlack/40 text-xs uppercase tracking-widest">
        &copy; {{ date('Y') }} {{ env('APP_NAME') }} Luxuries
    </div>

</body>

</html>
