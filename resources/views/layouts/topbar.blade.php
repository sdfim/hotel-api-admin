<nav
    class="border-b border-slate-100 dark:bg-zinc-800 print:hidden flex items-center fixed top-0 right-0 left-0 bg-white z-10 dark:border-zinc-700">

    <div class="flex items-center justify-between w-full">
        <div class="topbar-brand flex items-center">
            <div
                class="navbar-brand flex items-center justify-between shrink px-5 h-[70px] border-r bg-slate-50 border-r-gray-50 dark:border-zinc-700 dark:bg-zinc-800">
                <a href="#" class="flex items-center font-bold text-lg  dark:text-white">
                    <img src="{{ URL::asset('build/images/logo-sm.svg') }}" alt=""
                        class="ltr:mr-2 rtl:ml-2 inline-block mt-1 h-6" />
                    <span class="hidden xl:block align-middle">Minia</span>
                </a>
            </div>
            <button type="button"
                class="text-gray-600 dark:text-white h-[70px] ltr:-ml-10 ltr:mr-6 rtl:-mr-10 rtl:ml-10 vertical-menu-btn"
                id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <form class="app-search hidden xl:block px-5">
                <div class="relative inline-block">
                    <input type="text"
                        class="bg-gray-50/30 dark:bg-zinc-700/50 border-0 rounded focus:ring-0 placeholder:text-sm px-4 dark:placeholder:text-gray-200 dark:text-gray-100 dark:border-zinc-700 "
                        placeholder="Search...">
                    <button
                        class="py-1.5 px-2.5 text-white bg-violet-500 inline-block absolute ltr:right-1 top-1 rounded shadow shadow-violet-100 dark:shadow-gray-900 rtl:left-1 rtl:right-auto"
                        type="button"><i class="bx bx-search-alt align-middle"></i></button>
                </div>
            </form>
        </div>
        <div class="flex items-center">

            <div>
                <div class="dropdown relative sm:hidden block">
                    <button type="button"
                        class="text-xl px-4 h-[70px] text-gray-600 dark:text-gray-100 dropdown-toggle"
                        data-dropdown-toggle="navNotifications">
                        <i data-feather="search" class="h-5 w-5"></i>
                    </button>

                    <div class="dropdown-menu absolute px-4 -left-36 top-0 mx-4 w-72 z-50 hidden list-none border border-gray-50 rounded bg-white shadow dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300"
                        id="navNotifications">
                        <form class="py-3 dropdown-item" aria-labelledby="navNotifications">
                            <div class="form-group m-0">
                                <div class="flex w-full">
                                    <input type="text"
                                        class="border-gray-100 dark:border-zinc-600 dark:text-zinc-100 w-fit"
                                        placeholder="Search ..." aria-label="Search Result">
                                    <button
                                        class="btn btn-primary border-l-0 rounded-l-none bg-violet-500 border-transparent text-white"
                                        type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="dropdown relative language hidden sm:block">
                <button class="btn border-0 py-0 dropdown-toggle px-4 h-[70px]" type="button" aria-expanded="false"
                    data-dropdown-toggle="navNotifications">
                    <img src="{{ URL::asset('build/images/flags/us.jpg') }}" alt="" class="h-4"
                        id="header-lang-img">
                </button>
                <div class="dropdown-menu absolute -left-24 z-50 hidden w-40 list-none rounded bg-white shadow dark:bg-zinc-800"
                    id="navNotifications">
                    <ul class="border border-gray-50 dark:border-gray-700" aria-labelledby="navNotifications">
                        <li>
                            <a href="#"
                                class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-50/50 dark:text-gray-200 dark:hover:bg-zinc-600/50 dark:hover:text-white"><img
                                    src="{{ URL::asset('build/images/flags/us.jpg') }}" alt="user-image"
                                    class="mr-1 inline-block h-3"> <span class="align-middle">English</span></a>
                        </li>
                        <li>
                            <a href="#"
                                class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-50/50 dark:text-gray-200 dark:hover:bg-zinc-600/50 dark:hover:text-white"><img
                                    src="{{ URL::asset('build/images/flags/spain.jpg') }}" alt="user-image"
                                    class="mr-1 inline-block h-3"> <span class="align-middle">Spanish</span></a>
                        </li>
                        <li>
                            <a href="#"
                                class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-50/50 dark:text-gray-200 dark:hover:bg-zinc-600/50 dark:hover:text-white"><img
                                    src="{{ URL::asset('build/images/flags/germany.jpg') }}" alt="user-image"
                                    class="mr-1 inline-block h-3"> <span class="align-middle">German</span></a>
                        </li>
                        <li>
                            <a href="#"
                                class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-50/50 dark:text-gray-200 dark:hover:bg-zinc-600/50 dark:hover:text-white"><img
                                    src="{{ URL::asset('build/images/flags/italy.jpg') }}" alt="user-image"
                                    class="mr-1 inline-block h-3"> <span class="align-middle">Italian</span></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div>
                <button type="button"
                    class="light-dark-mode text-xl px-4 h-[70px] text-gray-600 dark:text-gray-100 hidden sm:block ">
                    <i data-feather="moon" class="h-5 w-5 block dark:hidden"></i>
                    <i data-feather="sun" class="h-5 w-5 hidden dark:block"></i>
            </div>

            <div>
                <div class="dropdown relative text-gray-600 hidden sm:block">
                    <button type="button" class="btn border-0 h-[70px] text-xl px-4 dropdown-toggle dark:text-gray-100"
                        data-bs-toggle="dropdown" id="dropdownMenuButton1">
                        <i data-feather="grid" class="h-5 w-5"></i>
                    </button>
                    <div class="dropdown-menu absolute -left-40 z-50 hidden w-72 list-none border border-gray-50 rounded bg-white shadow dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300"
                        aria-labelledby="dropdownMenuButton1">
                        <div class="p-2">
                            <div class="grid grid-cols-3">
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/github.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>GitHub</span>
                                </a>
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/bitbucket.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>Bitbucket</span>
                                </a>
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/dribbble.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                            <div class="grid grid-cols-3">
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/dropbox.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>Dropbox</span>
                                </a>
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/mail_chimp.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>Mail Chimp</span>
                                </a>
                                <a class="dropdown-item hover:bg-gray-50/50 py-4 text-center dark:hover:bg-zinc-700/50 dark:hover:text-gray-50"
                                    href="#">
                                    <img src="{{ URL::asset('build/images/brands/slack.png') }}"
                                        class="mb-2 mx-auto h-6" alt="Github">
                                    <span>Slack</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="dropdown relative ">
                    <div class="relative">
                        <button type="button"
                            class="btn border-0 h-[70px] dropdown-toggle px-4 text-gray-500 dark:text-gray-100"
                            aria-expanded="false" data-dropdown-toggle="notification">
                            <i data-feather="bell" class="h-5 w-5"></i>
                        </button>
                        <span
                            class="absolute text-xs px-1.5 bg-red-500 text-white font-medium rounded-full left-6 top-2.5">5</span>
                    </div>
                    <div class="dropdown-menu absolute z-50 hidden w-80 list-none rounded bg-white shadow dark:bg-zinc-800 "
                        id="notification">
                        <div class="border border-gray-50 dark:border-gray-700 rounded"
                            aria-labelledby="notification">
                            <div class="grid grid-cols-12 p-4">
                                <div class="col-span-6">
                                    <h6 class="m-0 text-gray-700 dark:text-gray-100"> Notifications </h6>
                                </div>
                                <div class="col-span-6 justify-self-end">
                                    <a href="#!" class="text-xs underline dark:text-gray-400"> Unread (3)</a>
                                </div>
                            </div>
                            <div class="max-h-56" data-simplebar>
                                <div>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="flex px-4 py-2 hover:bg-gray-50/50 dark:hover:bg-zinc-700/50">
                                            <div class="flex-shrink-0 ltr:mr-3 rtl:ml-3">
                                                <img src="{{ URL::asset('build/images/users/avatar-3.jpg') }}"
                                                    class="rounded-full h-8 w-8" alt="user-pic">
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-gray-700 dark:text-gray-100">James Lemire</h6>
                                                <div class="text-13 text-gray-600">
                                                    <p class="mb-1 dark:text-gray-400">It will seem like simplified
                                                        English.</p>
                                                    <p class="mb-0"><i
                                                            class="mdi mdi-clock-outline dark:text-gray-400"></i>
                                                        <span>1 hour ago</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="flex px-4 py-2 hover:bg-gray-50/50 dark:hover:bg-zinc-700/50">
                                            <div class="flex-shrink-0 ltr:mr-3 rtl:ml-3">
                                                <div class="h-8 w-8 bg-violet-500 rounded-full text-center">
                                                    <i class="bx bx-cart text-xl leading-relaxed text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-gray-700 dark:text-gray-100">Your order is placed
                                                </h6>
                                                <div class="text-13 text-gray-600">
                                                    <p class="mb-1 dark:text-gray-400">If several languages coalesce
                                                        the grammar</p>
                                                    <p class="mb-0"><i
                                                            class="mdi mdi-clock-outline dark:text-gray-400"></i>
                                                        <span>3 min ago</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="flex px-4 py-2 hover:bg-gray-50/50 dark:hover:bg-zinc-700/50">
                                            <div class="flex-shrink-0 ltr:mr-3 rtl:ml-3">
                                                <div class="h-8 w-8 bg-green-500 rounded-full text-center">
                                                    <i
                                                        class="bx bx-badge-check text-xl leading-relaxed text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-gray-700 dark:text-gray-100">Your item is shipped
                                                </h6>
                                                <div class="text-13 text-gray-600">
                                                    <p class="mb-1 dark:text-gray-400">If several languages coalesce
                                                        the grammar</p>
                                                    <p class="mb-0"><i
                                                            class="mdi mdi-clock-outline dark:text-gray-400"></i>
                                                        <span>3 min ago</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#!" class="text-reset notification-item">
                                        <div class="flex px-4 py-2 hover:bg-gray-50/50 dark:hover:bg-zinc-700/50">
                                            <div class="flex-shrink-0 ltr:mr-3 rtl:ml-3">
                                                <img src="{{ URL::asset('build/images/users/avatar-6.jpg') }}"
                                                    class="rounded-full h-8 w-8" alt="user-pic">
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-gray-700 dark:text-gray-100">Salena Layfield</h6>
                                                <div class="text-13 text-gray-600">
                                                    <p class="mb-1 dark:text-gray-400">As a skeptical Cambridge friend
                                                        of mine occidental.</p>
                                                    <p class="mb-0"><i
                                                            class="mdi mdi-clock-outline dark:text-gray-400"></i>
                                                        <span>1 hour ago</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="p-1 border-t grid border-gray-50 dark:border-zinc-600 justify-items-center">
                                <a class="btn border-0 text-violet-500" href="javascript:void(0)">
                                    <i class="mdi mdi-arrow-right-circle mr-1"></i> <span>View More..</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div>
                <div class="dropdown relative ltr:mr-4 rtl:ml-4">
                    <button type="button"
                        class="flex items-center px-4 py-5 border-x border-gray-50 bg-gray-50/30 dropdown-toggle dark:bg-zinc-700 dark:border-zinc-600 dark:text-gray-100"
                        id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="true">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="h-8 w-8 rounded-full ltr:xl:mr-2 rtl:xl:ml-2"
                                src="@if (Auth::user()->profile_photo_url) {{ Auth::user()->profile_photo_url }} @else https://ui-avatars.com/api/?name={{ Auth::user()->name }}&color=7F9CF5&background=EBF4FF @endif"
                                alt="{{ Auth::user()->name }}">
                        @endif
                        <span class="fw-medium hidden xl:block">{{ Auth::user()->name }}</span>
                        <i class="mdi mdi-chevron-down align-bottom hidden xl:block"></i>
                    </button>
                    <div class="dropdown-menu absolute top-0 ltr:-left-3 rtl:-right-3 z-50 hidden w-40 list-none rounded bg-white shadow dark:bg-zinc-800"
                        id="profile/log">
                        <div class="border border-gray-50 dark:border-zinc-600" aria-labelledby="navNotifications">
                            <div class="dropdown-item dark:text-gray-100">
                                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                                    <i class="mdi mdi-face-man text-16 align-middle mr-1"></i>{{ __('Profile') }}
                                </x-responsive-nav-link>
                            </div>
                            <div class="dropdown-item dark:text-gray-100">
                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                                        <i
                                            class="mdi mdi-key-variant text-16 align-middle mr-1"></i>{{ __('API Tokens') }}
                                    </x-responsive-nav-link>
                                @endif
                            </div>
                            <hr class="border-gray-50 dark:border-gray-700">
                            <div class="dropdown-item dark:text-gray-100">
                                <x-responsive-nav-link href="javascript:void();"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="mdi mdi-logout text-16 align-middle mr-1"></i>{{ __('Logout') }}
                                </x-responsive-nav-link>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
