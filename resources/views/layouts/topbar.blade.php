<nav
    class="main fixed inset-x-0 top-0 z-10 flex items-center justify-between border-b bg-white dark:border-zinc-700 dark:bg-zinc-800 print:hidden">
    <!-- Brand & burger -->
    <div class="topbar-brand flex items-center">
        <div
            class="navbar-brand flex h-[50px] shrink items-center justify-start border-r bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 px-0">
            <button type="button" id="vertical-menu-btn" class="vertical-menu-btn text-gray-100 dark:text-white">
                <i class="fa fa-fw fa-bars text-lg"></i>
            </button>
            <a href="#" class="logo-link flex items-center font-bold text-lg dark:text-white">
                <img src="{{ URL::asset('build/images/logo-vidanta.png') }}" alt="logo"
                    class="logo-lg inline-block h-6 mt-1" />
                <img src="{{ URL::asset('build/images/logo-vidanta-short.png') }}" alt="logo"
                    class="logo-sm inline-block h-6 mt-1" style="display: none;" />
            </a>
        </div>
    </div>

    <!-- Right section: search + user -->
    <div class="flex items-center space-x-2 px-4">
        <!-- Search (Filament-style) -->
        {{-- <form action="#" method="GET">--}}
            {{-- <div
                class="flex w-64 items-center gap-2 overflow-hidden rounded-lg border border-gray-300 bg-white px-3 transition-all"
                --}} {{-- :class="focused ? 'ring-2 ring-primary-500 border-primary-500' : ''" --}} {{--
                @click="focused = true" --}} {{-- @click.away="focused = false">--}}
                {{-- <span class="text-gray-500 dark:text-gray-400">--}}
                    {{-- <i data-feather="search" class="h-4 w-4"></i>--}}
                    {{-- </span>--}}
                {{-- <input--}} {{-- type="text" --}} {{-- name="search" --}} {{-- placeholder="Search" --}} {{--
                    x-model="query" --}} {{--
                    class="w-full border-none bg-transparent text-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-0 dark:text-gray-200 dark:placeholder-gray-400"
                    --}} {{-- @focus="focused = true" --}} {{-- />--}}
                {{-- <button--}} {{-- x-show="query.length > 0" --}} {{-- x-cloak--}} {{--
                    @click.prevent="query = ''; focused = false" --}} {{-- type="button" --}} {{--
                    class="text-gray-400 hover:text-gray-500">--}}
                    {{-- <i data-feather="x" class="h-4 w-4"></i>--}}
                    {{-- </button>--}}
                    {{-- </div>--}}
            {{-- </form>--}}

        <!-- User dropdown -->
        <div class="relative" x-data="{ open: false, bodyMode: localStorage.getItem('topbar-mode') || 'fixed' }"
            @click.away="open = false">
            <button id="user-dropdown-toggle" @click="open = !open"
                class="flex h-[50px] items-center border-gray-100 px-4 dark:border-zinc-600 dark:bg-zinc-800">
                <div class="user-toggle-content flex items-center">
                    <span class="hidden text-gray-100 dark:text-white xl:block text-sm">{{ Auth::user()->name }}</span>
                    <svg class="ml-1 h-4 w-4 text-gray-500 dark:text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <i class="fa fa-bars user-mode-icon hidden"></i>
            </button>

            <!-- Dropdown panel -->
            <div id="user-dropdown" x-show="open" x-transition
                class="absolute right-0 mt-2 w-64 space-y-2 rounded bg-white p-3 text-sm text-gray-800 shadow dark:bg-zinc-800 dark:text-gray-100"
                style="display:none;">
                <!-- Profile & API in single column -->
                <div class="flex flex-col gap-1">
                    <a href="{{ route('profile.show') }}"
                        class="flex items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700">
                        <i class="mdi mdi-account text-lg"></i>
                        <span>Profile</span>
                    </a>
                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <a href="{{ route('api-tokens.index') }}"
                            class="flex items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700">
                            <i class="mdi mdi-key-variant text-lg"></i>
                            <span>API</span>
                        </a>
                    @endif
                </div>

                <!-- Toggle theme -->
                <button type="button" @click="open = false; window.dispatchEvent(new Event('toggle-theme'));"
                    class="light-dark-mode flex w-full items-center space-x-2 rounded px-2 py-1 text-left hover:bg-gray-100 dark:hover:bg-zinc-700">
                    <i data-feather="moon" class="block h-5 w-5 dark:hidden"></i>
                    <i data-feather="sun" class="hidden h-5 w-5 dark:block"></i>
                    <span>Toggle Theme</span>
                </button>

                <!-- Topbar Mode Settings -->
                <div class="border-t border-gray-100 pt-2 mt-2 dark:border-zinc-700">
                    <p class="px-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Topbar Mode</p>
                    <div class="flex flex-col gap-1">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('set-topbar-mode', { detail: 'fixed' })); bodyMode = 'fixed';"
                            class="flex items-center space-x-2 rounded px-2 py-1 text-left hover:bg-gray-100 dark:hover:bg-zinc-700"
                            :class="bodyMode === 'fixed' ? 'bg-gray-100 dark:bg-zinc-700 font-bold' : ''">
                            <i data-feather="lock" class="h-4 w-4"></i>
                            <span>Always Present</span>
                        </button>
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('set-topbar-mode', { detail: 'dynamic' })); bodyMode = 'dynamic';"
                            class="flex items-center space-x-2 rounded px-2 py-1 text-left hover:bg-gray-100 dark:hover:bg-zinc-700"
                            :class="bodyMode === 'dynamic' ? 'bg-gray-100 dark:bg-zinc-700 font-bold' : ''">
                            <i data-feather="zap" class="h-4 w-4"></i>
                            <span>Dynamic</span>
                        </button>
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('set-topbar-mode', { detail: 'hidden' })); bodyMode = 'hidden';"
                            class="flex items-center space-x-2 rounded px-2 py-1 text-left hover:bg-gray-100 dark:hover:bg-zinc-700"
                            :class="bodyMode === 'hidden' ? 'bg-gray-100 dark:bg-zinc-700 font-bold' : ''">
                            <i data-feather="eye-off" class="h-4 w-4"></i>
                            <span>No Topbar</span>
                        </button>
                    </div>
                </div>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}"
                    class="border-t border-gray-100 pt-2 mt-2 dark:border-zinc-700">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center space-x-2 rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-zinc-700 text-red-500">
                        <i class="mdi mdi-logout text-lg"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Logic for Topbar and Theme -->
<script>
    const STORAGE_KEY = 'data-layout-mode';
    const TOPBAR_MODE_KEY = 'topbar-mode';

    function syncTheme(mode) {
        const root = document.documentElement;
        const body = document.body;

        if (mode === 'dark') {
            root.classList.add('dark');
            body.setAttribute('data-mode', 'dark');
        } else {
            root.classList.remove('dark');
            body.setAttribute('data-mode', 'light');
        }

        localStorage.setItem('theme', mode);
        sessionStorage.setItem(STORAGE_KEY, mode);
    }

    function setTopbarMode(mode) {
        const body = document.body;
        body.classList.remove('topbar-fixed', 'topbar-dynamic', 'topbar-hidden');
        body.classList.add(`topbar-${mode}`);
        localStorage.setItem(TOPBAR_MODE_KEY, mode);

        // Update Alpine state if available
        if (window.Alpine) {
            const dropdown = document.querySelector('[x-data]');
            if (dropdown && dropdown.__x) {
                dropdown.__x.$data.bodyMode = mode;
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        // Enhance User dropdown with bodyMode state
        const originalData = { open: false };
        const initialMode = localStorage.getItem(TOPBAR_MODE_KEY) || 'dynamic';

        window.addEventListener('toggle-theme', () => {
            const currentMode = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newMode = currentMode === 'dark' ? 'light' : 'dark';
            syncTheme(newMode);
        });

        window.addEventListener('set-topbar-mode', (e) => {
            setTopbarMode(e.detail);
        });
    });

    window.addEventListener('load', () => {
        // Sync Theme
        const savedMode = localStorage.getItem('theme') || sessionStorage.getItem(STORAGE_KEY) || 'light';
        syncTheme(savedMode);

        // Sync Topbar Mode
        const savedTopbarMode = localStorage.getItem(TOPBAR_MODE_KEY) || 'fixed';
        setTopbarMode(savedTopbarMode);

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });

    window.addEventListener('scroll', function () {
        if (document.body.classList.contains('topbar-dynamic')) {
            if (window.scrollY > 50) {
                document.body.classList.add('is-scrolled');
            } else {
                document.body.classList.remove('is-scrolled');
            }
        } else {
            document.body.classList.remove('is-scrolled');
        }
    });
</script>