<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\Strings;
use App\Models\Channel;
use App\Models\Enums\RoleSlug;
use App\Models\Mapping;
use App\Models\Property;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\MealPlansEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Livewire\Components\CustomTab;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Hotel $record;

    public bool $verified;

    public bool $onSale;

    public $showDeleteConfirmation = false;

    public $showModalLogInfoOnSale = false;

    public $showModalLogInfoProduct = false;

    public string $onSaleCausation = '';

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel->load('product');

        $this->verified = $hotel->product->verified ?? false;
        $this->onSale = $hotel->product->onSale ?? false;

        $data = $this->record->toArray();

        if ($this->record->address) {
            foreach ($this->record->address as $key => $value) {
                $data['addressArr'][$key] = $value;
            }
        } else {
            $data['addressArr'] = [];
        }

        $data['galleries'] = $this->record->product ? $this->record->product->galleries->pluck('id')->toArray() : [];
        $data['channels'] = $this->record->product ? $this->record->product->channels->pluck('id')->toArray() : [];
        foreach (SupplierNameEnum::getValuesDriver() as $supplier) {
            $data['off_save'][$supplier] = in_array($supplier, $this->record->product->off_sale_by_sources ?? []);
        }

        $this->form->fill($data);
    }

    public function toggleVerified()
    {
        $this->verified = ! $this->verified;
        $this->record->product->update(['verified' => $this->verified]);

        Notification::make()
            ->title('Verification Status Changed')
            ->body('The verification status has been successfully updated.')
            ->success()
            ->send();
    }

    public function toggleOnSale()
    {
        if ($this->onSale) {
            $this->dispatch('open-modal', id: 'open-modal-on-sale-causation');
        } else {
            if (! $this->record->product->vendor->verified) {
                Notification::make()
                    ->title('Action Denied')
                    ->body('Vendor must be verified to toggle OnSale status.')
                    ->danger()
                    ->send();

                return;
            }

            $this->onSale = ! $this->onSale;
            $this->record->product->update([
                'onSale' => $this->onSale,
                'on_sale_causation' => null,
            ]);
            Notification::make()
                ->title('Sale Status Changed')
                ->body('The sale status has been successfully updated to ON.')
                ->success()
                ->send();
        }
    }

    public function submitOnSaleForm()
    {
        $this->onSale = ! $this->onSale;
        $this->record->product->update([
            'onSale' => $this->onSale,
            'on_sale_causation' => $this->onSaleCausation,
        ]);
        Notification::make()
            ->title('Sale Status Changed')
            ->body('The sale status has been successfully updated to OFF.')
            ->warning()
            ->send();
    }

    public function confirmDeleteHotel()
    {
        $this->showDeleteConfirmation = true;
    }

    public function deleteHotel()
    {
        \DB::transaction(function () {
            $this->record->product->delete();
            $this->record->delete();
        });

        Notification::make()
            ->title('Hotel deleted successfully')
            ->success()
            ->send();

        $this->showDeleteConfirmation = false;

        return redirect()->route('hotel-repository.index');
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm())
            ->statePath('data')
            ->model($this->record)
            ->columns(1);
    }

    public function schemeForm(): array
    {
        $mapComponent = null;
        if (config('filament-google-maps.key')) {
            $mapComponent = Map::make('product.location')
                ->label('')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->handleReverseGeocoding($state, $set);
                })
                ->height(fn () => '300px')
                ->defaultZoom(17)
                ->autocomplete('full_address')
                ->autocompleteReverse(true)
                ->reverseGeocode([
                    'street' => '%n %S',
                    'city' => '%L',
                    'state' => '%A1',
                    'zip' => '%z',
                ])
                ->defaultLocation(fn () => [$this->data['product']['lat'] ?? 39.526610, $this->data['product']['lng'] ?? -107.727261])
                ->draggable()
                ->clickable(false)
                ->geolocate()
                ->geolocateLabel('Get Location')
                ->geolocateOnLoad(true, false)
                ->columnSpan(1);
        }

        $toggles = [];
        $mapperSupplier = Mapping::where('giata_id', $this->record->giata_code)
            ->whereIn('supplier', SupplierNameEnum::getValuesDriver())
            ->pluck('supplier')
            ->toArray();
        foreach ($mapperSupplier as $supplier) {
            $toggles[] = CustomToggle::make('off_save.'.$supplier)->label($supplier);
        }

        return [
            Tabs::make('Hotel Details')
                ->columns(1)
                ->tabs([
                    // Tab 1: Product
                    CustomTab::make('Product')
                        ->id('product')
                        ->schema([
                            Grid::make(2)
                                ->schema(self::getCoreFields($this->record)),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('product.name')
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Product Name');
                                        })
                                        ->label('Product Name')
                                        ->maxLength(191),
                                    //                                    Select::make('sale_type')
                                    //                                        ->label('Type')
                                    //                                        ->options([
                                    //                                            HotelSaleTypeEnum::DIRECT_CONNECTION->value => HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                                    //                                            HotelSaleTypeEnum::MANUAL_CONTRACT->value => HotelSaleTypeEnum::MANUAL_CONTRACT->value,
                                    //                                            HotelSaleTypeEnum::COMMISSION_TRACKING->value => HotelSaleTypeEnum::COMMISSION_TRACKING->value,
                                    //                                            HotelSaleTypeEnum::HYBRID_DIRECT_CONNECT_MANUAL_CONTRACT->value => HotelSaleTypeEnum::HYBRID_DIRECT_CONNECT_MANUAL_CONTRACT->value,
                                    //                                        ])->required()
                                    //                                        ->rule('required', function (Get $get, $state) {
                                    //                                            return self::validateRequiredField($get, $state, 'Type');
                                    //                                        }),
                                    TextInput::make('star_rating')
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Star Rating');
                                        })
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(6)
                                        ->inputMode('decimal')
                                        ->step(0.5)
                                        ->label('Star Rating'),
                                    TextInput::make('num_rooms')
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Number of Rooms');
                                        })
                                        ->numeric()
                                        ->label('Number of Rooms'),
                                    Select::make('hotel_board_basis')
                                        ->label('Meal Plans Available')
                                        ->multiple()
                                        ->options(array_combine(MealPlansEnum::values(), MealPlansEnum::values()))
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Meal Plans Available');
                                        }),
                                    FileUpload::make('product.hero_image')
                                        ->image()
                                        ->imageEditor()
                                        ->preserveFilenames()
                                        ->directory('products')
                                        ->disk(config('filament.default_filesystem_disk', 'public'))
                                        ->downloadable()
                                        ->columnSpan(1)
                                        ->visible()
                                        ->visibility('public')
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $originalPath = $state->storeAs('products', $state->getClientOriginalName());
                                                $thumbnailPath = 'products/thumbnails/'.$state->getClientOriginalName();
                                                $publicPath = Storage::url($originalPath);

                                                if (Storage::exists($originalPath)) {
                                                    $imageData = config('filament.default_filesystem_disk') === 's3'
                                                        ? Http::get($publicPath)->body()
                                                        : Storage::get($originalPath);

                                                    try {
                                                        $image = Image::read($imageData);
                                                        $image->resize(150, 150);
                                                        Storage::put($thumbnailPath, (string) $image->encode());
                                                        $set('product.hero_image_thumbnails', $thumbnailPath);
                                                    } catch (\Exception $e) {
                                                        \Log::error('Image decode error: '.$e->getMessage());
                                                    }
                                                }
                                            }
                                        }),
                                    TextInput::make('product.website')->url()->label('Website')->maxLength(191),
                                    Hidden::make('product.hero_image_thumbnails')->dehydrated(),
                                ]),

                            // from Data Sources tab
                            Grid::make(2)
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                            Actions::make([
                                                Action::make('current content viewer')
                                                    ->modalHeading('Viewer Property Hotel '
                                                        .$this->record->giata_code.' '.$this->record->product->name)
                                                    ->modalWidth('7xl')
                                                    ->modalContent(function () {
                                                        return view('dashboard.hotel_repository.modal-detail', [
                                                            'giataCode' => $this->data['giata_code'] ?? null,
                                                        ]);
                                                    })
                                                    ->modalSubmitAction(fn ($action) => $action->hidden())
                                                    ->modalCancelAction(fn ($action) => $action->hidden())
                                                    ->extraAttributes(['class' => 'h-12']),
                                            ]),
                                        ]),
                                    Grid::make(6)
                                        ->schema([
                                            Select::make('product.content_source_id')
                                                ->label('Content Source')
                                                ->options(ContentSource::whereIn('name', array_merge([ContentSourceEnum::INTERNAL->value], SupplierNameEnum::getValues()))->pluck('name', 'id')->toArray())
                                                ->required()
                                                ->rule('required', function (Get $get, $state) {
                                                    return self::validateRequiredField($get, $state, 'Content Source');
                                                })->columnSpan(5),
                                            Actions::make([
                                                Action::make('viewer')
                                                    ->modalHeading('Compare Content Hotel '
                                                        .$this->record->giata_code.' '.$this->record->product->name)
                                                    ->modalWidth('7xl')
                                                    ->modalContent(function () {
                                                        return view('dashboard.hotel_repository.modal-compare-content', [
                                                            'giataCode' => $this->data['giata_code'] ?? null,
                                                        ]);
                                                    })
                                                    ->modalSubmitAction(fn ($action) => $action->hidden())
                                                    ->modalCancelAction(fn ($action) => $action->hidden())
                                                    ->extraAttributes(['class' => 'h-12']),
                                            ])
                                                ->columnSpan(1)
                                                ->extraAttributes([
                                                    'class' => 'flex justify-end',
                                                ]),
                                        ])->columnSpan(1),

                                    Select::make('room_images_source_id')
                                        ->label('Room Images Source')
                                        ->placeholder('Select an option')
                                        ->options(ContentSource::whereIn('name', array_merge([ContentSourceEnum::INTERNAL->value], SupplierNameEnum::getValues()))->pluck('name', 'id')->toArray())
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Room Images Source');
                                        }),

                                    Grid::make(6)
                                        ->schema([
                                            Select::make('product.property_images_source_id')
                                                ->label('Property Images Source')
                                                ->options(ContentSource::whereIn('name', array_merge([ContentSourceEnum::INTERNAL->value], SupplierNameEnum::getValues()))->pluck('name', 'id')->toArray())
                                                ->required()
                                                ->rule('required', function (Get $get, $state) {
                                                    return self::validateRequiredField($get, $state, 'Property Images Source');
                                                })->columnSpan(5),
                                            Actions::make([
                                                Action::make('viewer ')
                                                    ->modalHeading('Compare Property Images Hotel '
                                                        .$this->record->giata_code.' '.$this->record->product->name)
                                                    ->modalWidth('5xl')
                                                    ->modalContent(function () {
                                                        return view('dashboard.hotel_repository.modal-compare-images', [
                                                            'giataCode' => $this->data['giata_code'] ?? null,
                                                        ]);
                                                    })
                                                    ->modalSubmitAction(fn ($action) => $action->hidden())
                                                    ->modalCancelAction(fn ($action) => $action->hidden())
                                                    ->extraAttributes(['class' => 'h-12']),
                                            ])
                                                ->columnSpan(1)
                                                ->extraAttributes([
                                                    'class' => 'flex justify-end',
                                                ]),
                                        ])->columnSpan(1),

                                    Select::make('product.default_currency')
                                        ->label('Default Currency')
                                        ->required()
                                        ->rule('required', function (Get $get, $state) {
                                            return self::validateRequiredField($get, $state, 'Default Currency');
                                        })
                                        ->options([
                                            'USD' => 'USD',
                                            'EUR' => 'EUR',
                                            'GBP' => 'GBP',
                                            'JPY' => 'JPY',
                                            'AUD' => 'AUD',
                                            'CAD' => 'CAD',
                                            'CHF' => 'CHF',
                                            'CNY' => 'CNY',
                                            'SEK' => 'SEK',
                                            'NZD' => 'NZD',
                                        ]),

                                    TextInput::make('weight')
                                        ->label('Weight')
                                        ->integer(),
                                    Grid::make(1)
                                        ->schema([
                                            CustomToggle::make('is_not_auto_weight')
                                                ->label('Do not update weight automatically'),
                                        ])
                                        ->extraAttributes(['class' => 'mt-10'])
                                        ->columnSpan(1),

                                    Select::make('channels')
                                        ->label('Channels')
                                        ->multiple()
                                        ->options(Channel::all()->sortBy('name')->pluck('name', 'id')),

                                    Section::make('Drivers')
                                        ->schema($toggles)
                                        ->columns(8),
                                ]),
                        ])
                        ->columns(1),

                    // Tab 2: Location
                    CustomTab::make('Location')
                        ->id('location')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                            TextInput::make('full_address')
                                                ->label('Get Location by Address'),
                                            TextInput::make('product.lat')->label('Latitude')->numeric()->readOnly(),
                                            TextInput::make('product.lng')->label('Longitude')->numeric()->readOnly(),
                                        ])->columnSpan(1),

                                    $mapComponent ?? Placeholder::make('map_message')
                                        ->label('Google Map')
                                        ->content('Please add GOOGLE_API_DEVELOPER_KEY to the .env file to display the Google Map and search coordinates by address.'),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('addressArr.city')
                                        ->label('City'),
                                    TextInput::make('addressArr.line_1')
                                        ->label('Line 1'),
                                    TextInput::make('addressArr.country_code')
                                        ->label('Country Code'),
                                    TextInput::make('addressArr.state_province_name')
                                        ->label('State Province Name'),
                                ]),
                        ])
                        ->columns(1),

                    // Tab 3: Data Sources
                    CustomTab::make('Data Sources')
                        ->id('data-sources')
                        ->schema([])
                        ->columns(2),
                ]),
            Actions::make([
                Action::make('save')
                    ->label(strtoupper($this->record->product ? 'Update Changes' : 'Save Changes'))
                    ->action(function () {
                        $this->record->product ? $this->edit() : $this->create();
                    })
                    ->extraAttributes([
                        'class' => 'save-button',
                    ])
                    ->visible(fn (Hotel $record) => Gate::allows('create', $record)),
            ]),
        ];
    }

    protected static function validateRequiredField(Get $get, $state, string $fieldName): bool
    {
        if (empty($state)) {
            Notification::make()
                ->title('Validation Error')
                ->body("The {$fieldName} field is required.")
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function getCoreFields($record = null): array
    {
        return [
            Select::make('giata_code')
                ->label('GIATA code')
                ->searchable()
                ->required()
                ->getSearchResultsUsing(function (string $search): ?array {
                    $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                    $result = Property::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                        ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                        ->limit(100);

                    return $result->pluck('full_name', 'code')
                        ->mapWithKeys(function ($full_name, $code) {
                            return [$code => $code.' ('.$full_name.')'];
                        })
                        ->toArray() ?? [];
                })
                ->getOptionLabelUsing(function (string $value): ?string {
                    $properties = Property::select(DB::raw('CONCAT(code, " (", name, ", location: ", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('code', $value)
                        ->first()
                        ->full_name ?? '';

                    return $properties;
                })
                ->disabled(fn () => $record),
            Select::make('product.vendor_id')
                ->label('Vendor Name')
                ->options(function ($record) {
                    $query = Vendor::query()
                        ->whereJsonContains('type', 'hotel');
                    if ($record?->product?->vendor->independent_flag) {
                        $query->where('id', $record->product->vendor->id);
                    } else {
                        $query->where('independent_flag', false);
                    }
                    if (auth()->user()->hasRole(RoleSlug::EXTERNAL_USER->value)) {
                        $query->where('name', auth()->user()->currentTeam->name);
                    }

                    return $query->orderBy('name')->pluck('name', 'id')->toArray();
                })
                ->dehydrated()
                ->required()
                ->rule('required', function (Get $get, $state) {
                    return self::validateRequiredField($get, $state, 'Vendor Name');
                }),
        ];
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        /** @var AddHotel $hotelAction */
        $hotelAction = app(AddHotel::class);
        $hotel = $hotelAction->create($data);

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-repository.edit', $hotel);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        /** @var AddHotel $hotelAction */
        $hotelAction = app(AddHotel::class);
        $hotel = $hotelAction->update($this->record, $data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        $referrerUrl = request()->header('referer');
        $tab = '-product-tab'; // Default value
        if ($referrerUrl) {
            $query = parse_url($referrerUrl, PHP_URL_QUERY);
            parse_str($query, $params);
            $tab = $params['tab'] ?? $tab;
        }

        return redirect()->route('hotel-repository.edit', ['hotel_repository' => $hotel, 'tab' => $tab]);
    }

    public function getGeocodingData(float $lat, float $lng): array
    {
        $addressArr = [];
        // Reverse geocoding logic
        $apiKey = config('filament-google-maps.key');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key={$apiKey}";

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        $streetNumber = $route = $city = $postal_town = $state_province_name = $zip = $country_code = '';

        if (! empty($results['results'][0]['address_components'])) {
            $components = $results['results'][0]['address_components'];

            // Populate address fields
            foreach ($components as $component) {
                if (in_array('street_number', $component['types'])) {
                    $streetNumber = $component['long_name'];
                }
                if (in_array('route', $component['types'])) {
                    $route = $component['long_name'];
                }
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                }
                if (in_array('postal_town', $component['types'])) {
                    $postal_town = $component['long_name'];
                }
                if (in_array('administrative_area_level_1', $component['types'])) {
                    $state_province_name = $component['long_name'];
                }
                if (in_array('postal_code', $component['types'])) {
                    $zip = $component['long_name'];
                }
                if (in_array('country', $component['types'])) {
                    $country_code = $component['short_name'];
                }
            }

            $addressArr['line_1'] = trim("$streetNumber $route, $zip");
            $addressArr['city'] = $city !== '' ? $city : $postal_town;
            $addressArr['state_province_name'] = $state_province_name;
            $addressArr['country_code'] = $country_code;
        }

        return $addressArr;
    }

    protected function handleReverseGeocoding(array $state, callable $set): void
    {
        if (isset($state['lat']) && isset($state['lng'])) {
            $set('product.lat', $state['lat']);
            $set('product.lng', $state['lng']);

            $addressArr = $this->getGeocodingData((float) $state['lat'], (float) $state['lng']);
            if (! empty($addressArr)) {
                $set('addressArr.line_1', $addressArr['line_1']);
                $set('addressArr.city', $addressArr['city']);
                $set('addressArr.state_province_name', $addressArr['state_province_name']);
                $set('addressArr.country_code', $addressArr['country_code']);
            }
        }
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-form');
    }
}
