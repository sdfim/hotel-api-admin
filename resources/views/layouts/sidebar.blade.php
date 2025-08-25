@php
    use App\Models\Configurations\ConfigRoomBedType;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Auth;
    use App\Models\GeneralConfiguration;
    use App\Models\Channel;
    use App\Models\Supplier;
    use App\Models\User;
    use App\Models\Role;
    use App\Models\Permission;
    use App\Models\Reservation;
    use App\Models\PricingRule;
    use App\Models\PropertyWeighting;
    use App\Models\ApiSearchInspector;
    use App\Models\ApiBookingInspector;
    use App\Models\ApiExceptionReport;
    use App\Models\Property;
    use App\Models\ExpediaContent;
    use Modules\HotelContentRepository\Models\Hotel;
    use Modules\HotelContentRepository\Models\Product;
    use Modules\HotelContentRepository\Models\Vendor;
    use App\Models\GiataGeography;
    use App\Models\Configurations\ConfigAttribute;
    use App\Models\Configurations\ConfigAttributeCategory;
    use App\Models\Configurations\ConfigAmenity;
    use App\Models\Configurations\ConfigConsortium;
    use App\Models\Configurations\ConfigDescriptiveType;
    use App\Models\Configurations\ConfigJobDescription;
    use App\Models\Configurations\ConfigChain;
    use App\Models\Configurations\ConfigInsuranceDocumentationType;
    use App\Models\Configurations\ConfigContactInformationDepartment;
    use Modules\HotelContentRepository\Models\KeyMappingOwner;
    use Modules\HotelContentRepository\Models\ImageGallery;
    use Modules\HotelContentRepository\Models\Image;
    use App\Models\Team;
    use App\Helpers\ClassHelper;

    $canView = fn (string|Model|null $model): bool => Auth::user()->can('view', $model);
    $canConfigurationGroup = fn (): bool =>
        $canView(GeneralConfiguration::class) ||
        $canView(Channel::class) ||
        $canView(Supplier::class) ||
        $canView(ConfigAttribute::class) ||
        $canView(ConfigAttributeCategory::class) ||
        $canView(ConfigAmenity::class) ||
        $canView(ConfigConsortium::class) ||
        $canView(ConfigDescriptiveType::class) ||
        $canView(ConfigJobDescription::class) ||
        $canView(ConfigChain::class);
        $canView(ConfigInsuranceDocumentationType::class);
        $canView(ConfigContactInformationDepartment::class);
        $canView(KeyMappingOwner::class);
        $canView(ConfigRoomBedType::class);
@endphp

@php
    $configurationLinks = collect([
        ['route' => 'general_configuration', 'text' => 'General', 'model' => GeneralConfiguration::class],
        ['route' => 'channels.index', 'text' => 'Channels', 'model' => Channel::class],
        ['route' => 'suppliers.index', 'text' => 'Suppliers', 'model' => Supplier::class],
        ['route' => 'configurations.attributes.index', 'text' => 'Attributes', 'model' => ConfigAttribute::class],
        ['route' => 'configurations.attribute-categories.index', 'text' => 'Attribute Categories', 'model' => ConfigAttributeCategory::class],
        ['route' => 'configurations.amenities.index', 'text' => 'Amenities', 'model' => ConfigAmenity::class],
        ['route' => 'configurations.descriptive-types.index', 'text' => 'Descriptive Types', 'model' => ConfigDescriptiveType::class],
        ['route' => 'configurations.job-descriptions.index', 'text' => 'Departments', 'model' => ConfigJobDescription::class],
        ['route' => 'configurations.external-identifiers.index', 'text' => 'External Identifiers', 'model' => KeyMappingOwner::class],
        ['route' => 'configurations.room-bed-types.index', 'text' => 'Bed Types in Room', 'model' => ConfigRoomBedType::class],
        ['route' => 'configurations.contact-information-departments.index', 'text' => 'TerraMare Departments', 'model' => ConfigContactInformationDepartment::class],
    ]);

    $fixedLinks = $configurationLinks->filter(function ($link) {
        return in_array($link['text'], ['General', 'Channels']);
    });

    $sortedLinks = $configurationLinks->filter(function ($link) {
        return !in_array($link['text'], ['General', 'Channels']);
    })->sortBy('text');

    $configurationLinks = $fixedLinks->merge($sortedLinks);
@endphp
    <!-- ========== Left Sidebar Start ========== -->
<div
    class="vertical-menu rtl:right-0 fixed ltr:left-0 bottom-0 h-screen border-r bg-slate-50 border-gray-50 print:hidden dark:bg-zinc-800 dark:border-neutral-700 z-10"
    style="top: 65px;">

    <div data-simplebar class="h-full">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu" id="side-menu">
                @if($canConfigurationGroup())
                    <li>
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-gear"></i>
                            <span data-key="t-configuration">Configuration</span>
                        </a>
                        <ul>
                            @foreach($configurationLinks as $link)
                                @if($canView($link['model']))
                                    <li>
                                        <a href="{{ route($link['route']) }}"
                                           class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                            {{ $link['text'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>

                @endif
                @if($canView(User::class) || $canView(Role::class) || $canView(Permission::class))
                    <li>
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-user-group"></i>
                            <span data-key="t-configuration">Users and permissions</span>
                        </a>
                        <ul>
                            @if($canView(User::class))
                                <li>
                                    <a href="{{ Route('users.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px]
                               font-medium text-gray-700 transition-all
                               duration-150 ease-linear hover:text-mandarin-500
                               dark:text-gray-300 dark:active:text-white
                               dark:hover:text-white">Users</a>
                                </li>
                            @endif
                            @if($canView(Role::class))
                                <li>
                                    <a href="{{ Route('roles.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px]
                               font-medium text-gray-700 transition-all
                               duration-150 ease-linear hover:text-mandarin-500
                               dark:text-gray-300 dark:active:text-white
                               dark:hover:text-white">Roles</a>
                                </li>
                            @endif
                            @if($canView(Permission::class))
                                <li>
                                    <a href="{{ Route('permissions.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px]
                               font-medium text-gray-700 transition-all
                               duration-150 ease-linear hover:text-mandarin-500
                               dark:text-gray-300 dark:active:text-white
                               dark:hover:text-white">Permissions</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($canView(Reservation::class))
                    <li>
                        <a href="{{ Route('reservations.index') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-pin"></i>
                            <span data-key="t-reservations"> Reservations</span>
                        </a>
                    </li>
                @endif
                @if($canView(PricingRule::class))
                    <li>
                        <a href="{{ Route('pricing-rules.index') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-network-3"></i>
                            <span data-key="t-pricing-rules"> Pricing Rules</span>
                        </a>
                    </li>
                @endif

                @if($canView(PropertyWeighting::class))
                    <li>
                        <a href="{{ Route('property-weighting.index') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-weight"></i>
                            <span data-key="t-property-weighting"> Property Weighting</span>
                        </a>
                    </li>
                @endif

                @if($canView(ApiSearchInspector::class) || $canView(ApiBookingInspector::class) || $canView(ApiBookingItem::class))
                    <li class="@if(Route::currentRouteName() == 'booking-inspector.show' || Route::currentRouteName() == 'search-inspector.show' || Route::currentRouteName() == 'booking-items.show') mm-active @endif">
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-archive"></i>
                            <span data-key="t-inspector">Inspectors</span>
                        </a>
                        <ul>
                            @if($canView(ApiSearchInspector::class))
                                <li>
                                    <a href="{{ Route('search-inspector.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'search-inspector.show') active @endif">
                                        Search Inspector</a>
                                </li>
                            @endif
                            @if($canView(ApiBookingInspector::class))
                                <li>
                                    <a href="{{ Route('booking-inspector.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'booking-inspector.show') active @endif">
                                        Booking Inspector</a>
                                </li>
                            @endif
                            @if($canView(ApiBookingItem::class))
                                <li>
                                    <a href="{{ Route('booking-items.index') }}"
                                       class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'booking-items.show') active @endif">
                                        Booking Items</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($canView(ApiExceptionReport::class))
                    <li class="@if(Route::currentRouteName() == 'exceptions-report.show') mm-active @endif">
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-graph-line"></i>
                            <span data-key="t-inspector">Exceptions Report</span>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ Route('exceptions-report.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'search-inspector.show') active @endif">
                                    Data</a>
                            </li>
                            <li>
                                <a href="{{ Route('exceptions-report-chart.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white @if(Route::currentRouteName() == 'booking-inspector.show') active @endif">
                                    Chart</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if($canView(ImageGallery::class) || $canView(Image::class))
                    <li>
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-photo-group"></i>
                            <span>Image Galleries</span>
                        </a>
                        <ul>
                            @if($canView(ImageGallery::class))
                                <li>
                                    <a href="{{ Route('image-galleries.index') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass() }}">
                                        <i class="dripicons-view-thumb"></i>
                                        <span>Galleries</span>
                                    </a>
                                </li>
                            @endif
                            @if($canView(Image::class))
                                <li>
                                    <a href="{{ Route('images.index') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass() }}">
                                        <i class="dripicons-photo"></i>
                                        <span>Images</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <li>
                    <a href="javascript: void(0);" aria-expanded="false"
                       class="nav-menu pl-6 pr-4 py-3 block text-sm font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                        <i class="dripicons-contract-2"></i>
                        <span data-key="t-property-mapping">Content Suppliers</span>
                    </a>
                    <ul>
                        @if(str_contains(config('booking-suppliers.connected_suppliers'), \Modules\Enums\SupplierNameEnum::EXPEDIA->value))
                            <li>
                                <a href="{{ Route('expedia.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                    Expedia
                                </a>
                            </li>
                        @endif
                        @if(str_contains(config('booking-suppliers.connected_suppliers'), \Modules\Enums\SupplierNameEnum::ICE_PORTAL->value))
                            <li>
                                <a href="{{ Route('ice-portal.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                    Ice Portal
                                </a>
                            </li>
                        @endif
                        @if(str_contains(config('booking-suppliers.connected_suppliers'), \Modules\Enums\SupplierNameEnum::HOTEL_TRADER->value))
                            <li>
                                <a href="{{ Route('hotel-trader.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                    Hotel Trader
                                </a>
                            </li>
                        @endif
                        @if(str_contains(config('booking-suppliers.connected_suppliers'), \Modules\Enums\SupplierNameEnum::HBSI->value))
                            <li>
                                <a href="{{ Route('hbsi-property.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                    HBSI
                                </a>
                        @endif
                        @if(str_contains(config('booking-suppliers.connected_suppliers'), \Modules\Enums\SupplierNameEnum::HILTON->value))
                            </li>
                            <li>
                                <a href="{{ Route('hilton.index') }}"
                                   class="pl-14 pr-4 py-2 block text-[13.5px] font-medium text-gray-700 transition-all duration-150 ease-linear hover:text-mandarin-500 dark:text-gray-300 dark:active:text-white dark:hover:text-white">
                                    Hilton
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                @if($canView(Property::class))
                    <li>
                        <a href="{{ Route('properties.index') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-map"></i>
                            <span data-key="t-property-mapping">Giata Properties</span>
                        </a>
                    </li>
                @endif

                @if($canView(Vendor::class)
                    || $canView(Product::class))
                    <li>
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-graduation"></i>
                            <span>Manual Content</span>
                        </a>
                        <ul>
                            @if($canView(Vendor::class))
                                <li>
                                    <a href="{{ Route('vendor-repository.index') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass() }}">
                                        <i class="dripicons-rocket"></i>
                                        <span>Vendors</span>
                                    </a>
                                </li>
                            @endif
                            @if($canView(Hotel::class))
                                <li>
                                    <a href="{{ Route('hotel-repository.index') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass()}}">
                                        <i class="dripicons-store"></i>
                                        <span>Hotels</span>
                                    </a>
                                </li>
                            @endif
                            {{--                            @if($canView(Product::class))--}}
                            {{--                                <li>--}}
                            {{--                                    <a href="{{ Route('pd-grid.index') }}"--}}
                            {{--                                       class="{{ ClassHelper::sidebarCildrenClass() }}">--}}
                            {{--                                        <i class="dripicons-to-do"></i>--}}
                            {{--                                        <span>PD Grid</span>--}}
                            {{--                                    </a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}
                        </ul>
                    </li>
                @endif

                @can('statistic-charts')
                    <li>
                        <a href="{{ Route('statistic-charts') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-graph-pie"></i>
                            <span data-key="t-statistic-charts"> Statistic charts</span>
                        </a>
                    </li>
                @endcan
                @if($canView(GiataGeography::class))
                    <li>
                        <a href="{{ Route('geography') }}"
                           class="{{ ClassHelper::sidebarPointClass() }}">
                            <i class="dripicons-direction"></i>
                            <span data-key="t-geography"> Geography</span>
                        </a>
                    </li>
                @endif
                @canany(['log-viewer', 'swagger-docs', 'activities'])
                    <li>
                        <a href="javascript: void(0);" aria-expanded="false"
                           class="{{ ClassHelper::sidebarParrentClass() }}">
                            <i class="dripicons-warning"></i>
                            <span data-key="t-tools">Tools</span>
                        </a>
                        <ul>
                            @can('log-viewer')
                                <li>
                                    <a href="{{ url('admin/log-viewer') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass() }}">
                                        <i class="dripicons-document-remove"></i>
                                        <span data-key="t-log-viewer">Log Viewer</span>
                                    </a>
                                </li>
                            @endcan
                            @can('log-viewer')
                                <li>
                                    <a href="{{ url('admin/activities') }}"
                                       class="{{ ClassHelper::sidebarCildrenClass() }}">
                                        <i class="dripicons-document"></i>
                                        <span data-key="t-log-viewer">Activity Log</span>
                                    </a>
                                </li>
                            @endcan
                            @can('swagger-docs')
                                <li>
                                    <a href="javascript: void(0);" aria-expanded="false"
                                       class="{{ ClassHelper::sidebarCildrenP2Class() }}">
                                        <i class="dripicons-document-edit"></i>
                                        <span data-key="t-api-documentation">Swagger</span>
                                    </a>
                                    <ul>
                                        <li>
                                            <a href="{{ url(config('app.url').'/admin/api/documentation') }}"
                                               class="{{ ClassHelper::sidebarCildrenL2Class() }}">
                                                Main Documentation
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ url(config('app.url').'/admin/api/doc-content-repository') }}"
                                               class="{{ ClassHelper::sidebarCildrenL2Class() }}">
                                                Manual Content
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
            </ul>
        </div>
    </div>
</div>
