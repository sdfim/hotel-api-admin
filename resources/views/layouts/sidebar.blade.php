<!-- ========== Left Sidebar Start ========== -->
<div
    class="vertical-menu rtl:right-0 fixed ltr:left-0 bottom-0 top-16 h-screen border-r bg-slate-50 border-gray-50 print:hidden dark:bg-zinc-800 dark:border-neutral-700 z-10">

    <div data-simplebar class="h-full">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu" id="side-menu">
                <li>
                    <a href="javascript: void(0);" aria-expanded="false"
                       class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-gear"></i>
                        <span data-key="t-configuration">Configuration</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ Route('general_configuration') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">General
                                Configuration</a>
                        </li>
                        <li>
                            <a href="{{ Route('channels.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Channels
                                Configuration</a>
                        </li>
                        <li>
                            <a href="{{ Route('suppliers.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Suppliers
                                Configuration</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ Route('reservations.index') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-pin"></i>
                        <span data-key="t-reservations"> Reservations</span>
                    </a>
                </li>

                <li class="@if(Route::currentRouteName() == 'booking-inspector.show' || Route::currentRouteName() == 'search-inspector.show') mm-active @endif">
                    <a href="javascript: void(0);" aria-expanded="false"
                       class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-archive"></i>
                        <span data-key="t-inspector">Inspectors</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ Route('search-inspector.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'search-inspector.show') active @endif">
                                Search Inspector</a>
                        </li>
                        <li>
                            <a href="{{ Route('booking-inspector.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'booking-inspector.show') active @endif">
                                Booking Inspector</a>
                        </li>
                    </ul>
                </li>
                <li class="@if(Route::currentRouteName() == 'content-loader-exceptions.show') mm-active @endif">
                    <a href="{{ Route('content-loader-exceptions.index') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-graph-line"></i>
                        <span data-key="t-content-loader-exception"> Exceptions Report</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" aria-expanded="false"
                       class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-map"></i>
                        <span data-key="t-property-mapping">Property Mapping</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ Route('giata.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                Giata
                            </a>
                        </li>
                        <li>
                            <a href="{{ Route('expedia.index') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                Expedia
                            </a>
                        </li>
						<li>
                            <a href="{{ Route('expedia_charts') }}"
                               class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                Charts
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ Route('geography') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-direction"></i>
                        <span data-key="t-geography"> Geography</span>
                    </a>
                </li>
                {{--<li>
                    <a href="{{ Route('content') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-blog"></i>
                        <span data-key="t-content"> Content</span>
                    </a>
                </li>--}}
                <li>
                    <a href="{{ Route('pricing_rules.index') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-network-3"></i>
                        <span data-key="t-pricing-rules"> Pricing Rule</span>
                    </a>
                </li>
                <li>
                    <a href="{{ Route('property-weighting.index') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-weight"></i>
                        <span data-key="t-log-viewer"> Property Weighting</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('log-viewer') }}"
                       class="pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-document-remove"></i>
                        <span data-key="t-log-viewer"> Log Viewer</span>
                    </a>
                </li>

                <!--

                <li>
                    <a href="javascript: void(0);" aria-expanded="false"  class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i data-feather="users"></i>
                        <span data-key="t-auth">Authentication</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ url('login') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Login</a>
                        </li>
                         <li>
                            <a href="{{ url('recoverpw') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Recover Password</a>
                        </li>
                        <li>
                            <a href="{{ url('lock-screen') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Lock Screen</a>
                        </li>
                        <li>
                            <a href="{{ url('logout') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Log Out</a>
                        </li>
                        <li>
                            <a href="{{ url('confirm-mail') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Confirm Mail</a>
                        </li>
                        <li>
                            <a href="{{ url('email-verification') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Email Verification</a>
                        </li>
                        <li>
                            <a href="{{ url('two-step-verification') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Two Step Verification</a>
                        </li>
                    </ul>
                </li> -->

                <!-- <li>
                    <a href="javascript: void(0);" aria-expanded="false" class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i data-feather="briefcase"></i><span data-key="t-pages">Pages</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ url('starter') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Starter Page</a>
                        </li>
                        <li>
                            <a href="{{ url('maintenance') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Maintenance</a>
                        </li>
                         <li>
                            <a href="{{ url('coming-soon') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Coming Soon</a>
                        </li>
                        <li>
                            <a href="{{ url('timeline') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Timeline</a>
                        </li>
                        <li>
                            <a href="{{ url('faqs') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">FAQs</a>
                        </li>
                        <li>
                            <a href="{{ url('pricing') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Pricing</a>
                        </li>
                        <li>
                            <a href="{{ url('404') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Error 404</a>
                        </li>
                        <li>
                            <a href="{{ url('500') }}" class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-violet-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">Error 500</a>
                        </li>
                    </ul>
                </li> -->

            </ul>

            <!-- <div class="sidebar-alert text-center mx-5 my-12">
                <div class="card-body bg-primary rounded bg-violet-50/50 dark:bg-zinc-700/60">
                    <img src="{{ URL::asset('build/images/giftbox.png') }}" alt="" class="block mx-auto">
                    <div class="mt-4">
                        <h5 class="text-violet-500 mb-3 font-medium">Unlimited Access</h5>
                        <p class="text-slate-600 text-13 dark:text-gray-50">Upgrade your plan from a Free trial, to select ‘Business Plan’.</p>
                        <a href="#!" class="btn bg-violet-500 text-white border-transparent mt-6">Upgrade Now</a>
                    </div>
                </div>
            </div> -->
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
