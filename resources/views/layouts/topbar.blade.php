<nav class="main fixed inset-x-0 top-0 z-10 flex items-center justify-between border-b bg-white dark:border-zinc-700 dark:bg-zinc-800 print:hidden">
    <!-- Brand & burger -->
    <div class="topbar-brand flex items-center">
        <div class="navbar-brand flex h-[50px] shrink items-center justify-between border-r bg-slate-50 px-5 dark:border-zinc-700 dark:bg-zinc-800">
            <a href="#" class="flex items-center font-bold text-lg dark:text-white">
                <img src="{{ URL::asset('build/images/logo-sm.svg') }}" alt="logo" class="inline-block h-6 ltr:mr-2 rtl:ml-2 mt-1" />
                <span class="hidden align-middle xl:block">TerraMare</span>
            </a>
        </div>
        <button type="button" id="vertical-menu-btn" class="vertical-menu-btn h-[50px] ltr:-ml-10 ltr:mr-6 rtl:-mr-10 rtl:ml-10 text-gray-600 dark:text-white">
            <i class="fa fa-fw fa-bars"></i>
        </button>
    </div>

    <!-- Right section: search + user -->
    <div class="flex items-center space-x-2 px-4">
        <!-- Search (Filament-style) -->
{{--        <form action="#" method="GET">--}}
{{--            <div class="flex w-64 items-center gap-2 overflow-hidden rounded-lg border border-gray-300 bg-white px-3 transition-all"--}}
{{--                 :class="focused ? 'ring-2 ring-primary-500 border-primary-500' : ''"--}}
{{--                 @click="focused = true"--}}
{{--                 @click.away="focused = false">--}}
{{--                <span class="text-gray-500 dark:text-gray-400">--}}
{{--                    <i data-feather="search" class="h-4 w-4"></i>--}}
{{--                </span>--}}
{{--                <input--}}
{{--                    type="text"--}}
{{--                    name="search"--}}
{{--                    placeholder="Search"--}}
{{--                    x-model="query"--}}
{{--                    class="w-full border-none bg-transparent text-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-0 dark:text-gray-200 dark:placeholder-gray-400"--}}
{{--                    @focus="focused = true"--}}
{{--                />--}}
{{--                <button--}}
{{--                    x-show="query.length > 0"--}}
{{--                    x-cloak--}}
{{--                    @click.prevent="query = ''; focused = false"--}}
{{--                    type="button"--}}
{{--                    class="text-gray-400 hover:text-gray-500">--}}
{{--                    <i data-feather="x" class="h-4 w-4"></i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </form>--}}

        <!-- User dropdown -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button id="user-dropdown-toggle" @click="open = !open" class="flex h-[50px] items-center border-x border-gray-100 px-4 dark:border-zinc-600 dark:bg-zinc-800">
                <img class="mr-2 h-8 w-8 rounded-full" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                <span class="hidden text-gray-800 dark:text-white xl:block text-sm">{{ Auth::user()->name }}</span>
                <svg class="ml-1 h-4 w-4 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown panel -->
            <div id="user-dropdown" x-show="open" x-transition class="absolute right-0 mt-2 w-64 space-y-2 rounded bg-white p-3 text-sm text-gray-800 shadow dark:bg-zinc-800 dark:text-gray-100" style="display:none;">
                <!-- Profile & API in one row -->
                <div class="flex justify-between gap-2">
                    <a href="{{ route('profile.show') }}" class="flex flex-1 items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700">
                        <i class="mdi mdi-account text-lg"></i>
                        <span>Profile</span>
                    </a>
                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <a href="{{ route('api-tokens.index') }}" class="flex flex-1 items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700">
                            <i class="mdi mdi-key-variant text-lg"></i>
                            <span>API</span>
                        </a>
                    @endif
                </div>

                <!-- Toggle theme -->
                <button type="button" @click="open = false; window.dispatchEvent(new Event('toggle-theme'));" class="light-dark-mode flex w-full items-center space-x-2 rounded px-2 py-1 text-left hover:bg-gray-100 dark:hover:bg-zinc-700">
                    <i data-feather="moon" class="block h-5 w-5 dark:hidden"></i>
                    <i data-feather="sun" class="hidden h-5 w-5 dark:block"></i>
                    <span>Toggle Theme</span>
                </button>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700">
                        <i class="mdi mdi-logout text-lg"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Feather & Alpine initialisation -->
<script>
    document.addEventListener('alpine:init', () => {
        window.addEventListener('toggle-theme', () => {
            const root = document.documentElement;
            root.classList.toggle('dark');
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        });
    });

    window.addEventListener('load', () => {
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
