<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote Approved -  <?= env('APP_NAME'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Load Meno Banner font --}}
    <style>
        @font-face {
            font-family: 'Meno Banner';
            src: url('/fonts/meno-banner-regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
    </style>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Tailwind custom configuration --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        meno: ['"Meno Banner"', 'serif'],
                    },
                    colors: {
                        brandDark: '#263A3A',
                        brandBg: '#C7D5C7',
                    },
                },
            },
        }
    </script>
</head>

<body class="min-h-screen bg-brandBg flex flex-col">

{{-- Top bar with logo in the right corner --}}
<header class="w-full flex justify-end items-center pt-4 pr-4 md:pt-6 md:pr-10">
    <img
        src="{{ asset('images/terra-mare-logo.png') }}"
        alt=" <?= env('APP_NAME'); ?>"
        class="h-8 sm:h-10 md:h-12 object-contain"
    >
</header>

{{-- Centered card area --}}
<main class="flex-1 flex items-center justify-center px-4 pb-8">
    <div
        class="bg-white rounded-3xl px-6 py-10
                   sm:px-10 sm:py-12
                   md:px-16 md:py-14
                   max-w-[740px] w-full text-center text-brandDark font-meno"
    >
        <h1 class="text-[32px] sm:text-[40px] md:text-[64px] leading-[1.5] font-normal mb-6">
            Thank You!
        </h1>

        <p class="text-[16px] sm:text-[18px] md:text-[20px] leading-[1.5] font-normal mb-4">
            Your quote has been approved.
        </p>

        <p class="text-[16px] sm:text-[18px] md:text-[20px] leading-[1.5] font-normal">
            We will send your client a payment link to reserve the booking and you will
            receive a confirmation email once it has been completed.
        </p>
    </div>
</main>

</body>
</html>
