<nav
    class="main fixed inset-x-0 top-0 z-10 flex items-center justify-between border-b dark:border-zinc-700 print:hidden">
    <!-- Brand & burger -->
    <div class="topbar-brand flex items-center">
        <div class="navbar-brand flex h-[50px] shrink items-center justify-start border-r dark:border-zinc-700 px-0">
            <button type="button" id="vertical-menu-btn" class="vertical-menu-btn text-gray-100 dark:text-white">
                <i class="fa fa-fw fa-bars text-lg"></i>
            </button>
            <a href="#" class="logo-link flex items-center font-bold text-lg dark:text-white">
                <img src="{{ URL::asset('build/images/logo-vidanta.png') }}" alt="logo"
                    class="logo-lg inline-block h-6" />
                <img src="{{ URL::asset('build/images/logo-vidanta-short.png') }}" alt="logo"
                    class="logo-sm inline-block h-6" style="display: none;" />
            </a>
        </div>
    </div>

    <!-- Breadcrumbs Section (Center) -->
    <div class="flex-1 px-4 hidden md:block">
        {{-- Breadcrumbs removed from topbar as per user request --}}
    </div>

    <!-- Right section: user menu (hidden via CSS when scrolled or in hidden mode) -->
    <div class="topbar-right-section flex items-center space-x-2 px-4 h-[50px]">
        <div class="relative" x-data="{ open: false, bodyMode: localStorage.getItem('topbar-mode') || 'fixed' }"
            @click.away="open = false" @set-topbar-mode.window="open = false" @toggle-theme.window="open = false">
            <button id="user-dropdown-toggle" @click="open = !open"
                class="flex h-[50px] items-center border-gray-100 px-4 dark:border-zinc-600">
                <div class="user-toggle-content flex items-center">
                    <span
                        class="hidden text-gray-700 dark:text-white xl:block text-sm font-medium">{{ Auth::user()->name }}</span>
                    <svg class="ml-1 h-4 w-4 text-gray-500 dark:text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            <!-- Dropdown panel -->
            <div id="user-dropdown" x-show="open" x-transition x-cloak
                class="absolute right-0 mt-2 w-64 space-y-2 rounded bg-white p-3 text-sm text-gray-800 shadow-xl ring-1 ring-black ring-opacity-5 dark:bg-zinc-800 dark:text-gray-100 z-[10006]">
                <!-- Profile & API in single column -->
                <div class="flex flex-col gap-1">
                    <a href="{{ route('profile.show') }}" @click="open = false"
                        class="flex items-center rounded px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-zinc-700">
                        <i class="mdi mdi-account-outline text-lg mr-2 w-5 text-center"></i>
                        <span>Profile</span>
                    </a>
                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <a href="{{ route('api-tokens.index') }}" @click="open = false"
                            class="flex items-center rounded px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-zinc-700">
                            <i class="mdi mdi-key-variant text-lg mr-2 w-5 text-center"></i>
                            <span>API</span>
                        </a>
                    @endif
                </div>

                <!-- Toggle theme -->
                <button type="button" @click="open = false; window.dispatchEvent(new Event('toggle-theme'));"
                    class="flex w-full items-center rounded px-2 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-zinc-700">
                    <i class="mdi mdi-theme-light-dark mr-2 text-lg w-5 text-center"></i>
                    <span>Toggle Theme</span>
                </button>

                <!-- Topbar Mode Settings -->
                <div class="border-t border-gray-100 pt-2 mt-2 dark:border-zinc-700">
                    <p class="px-2 pb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Topbar Mode</p>
                    <div class="flex flex-col gap-1">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('set-topbar-mode', { detail: 'fixed' })); bodyMode = 'fixed'; open = false;"
                            class="flex w-full items-center px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-zinc-700"
                            :class="bodyMode === 'fixed' ? 'bg-gray-100 dark:bg-zinc-700 font-bold' : ''">
                            <i class="mdi mdi-pin-outline mr-2 text-lg w-5 text-center"></i> Always Present
                        </button>
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('set-topbar-mode', { detail: 'hidden' })); bodyMode = 'hidden'; open = false;"
                            class="flex w-full items-center px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-zinc-700"
                            :class="bodyMode === 'hidden' ? 'bg-gray-100 dark:bg-zinc-700 font-bold' : ''">
                            <i class="mdi mdi-eye-off-outline mr-2 text-lg w-5 text-center"></i> No Topbar
                        </button>
                    </div>
                </div>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" @submit="open = false"
                    class="border-t border-gray-100 pt-2 mt-2 dark:border-zinc-700">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-zinc-700 text-red-500 text-sm font-medium">
                        <i class="mdi mdi-logout mr-2 text-lg w-5"></i> Logout
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
        let savedTopbarMode = localStorage.getItem(TOPBAR_MODE_KEY) || 'fixed';
        if (savedTopbarMode === 'dynamic') savedTopbarMode = 'fixed';
        setTopbarMode(savedTopbarMode);

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });

    window.addEventListener('scroll', function () {
        document.body.classList.remove('is-scrolled');
    });
</script>