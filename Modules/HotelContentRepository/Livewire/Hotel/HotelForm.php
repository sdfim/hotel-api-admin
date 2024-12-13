<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\Strings;
use App\Models\Channel;
use App\Models\Configurations\ConfigJobDescription;
use App\Models\Enums\RoleSlug;
use App\Models\Property;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\MealPlansEnum;
use Modules\Enums\HotelSaleTypeEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomTab;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Modules\HotelContentRepository\Models\Vendor;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public Hotel $record;
    public bool $verified;
    public $showDeleteConfirmation = false;

    public function __construct()
    {
        $this->record = new Hotel();
    }

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel->load('product');

        $this->verified = $hotel->product->verified ?? false;

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

        $this->form->fill($data);
    }

    public function toggleVerified()
    {
        $this->verified = !$this->verified;
        $this->record->product->update(['verified' => $this->verified]);
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
                                    TextInput::make('product.name')->required()->label('Product Name')->maxLength(191),
                                    Select::make('sale_type')
                                        ->label('Type')
                                        ->options([
                                            HotelSaleTypeEnum::DIRECT_CONNECTION->value => HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                                            HotelSaleTypeEnum::MANUAL_CONTRACT->value => HotelSaleTypeEnum::MANUAL_CONTRACT->value,
                                            HotelSaleTypeEnum::COMMISSION_TRACKING->value => HotelSaleTypeEnum::COMMISSION_TRACKING->value,
                                        ])->required(),
                                    TextInput::make('star_rating')->required()->numeric()->label('Star Rating'),
                                    TextInput::make('num_rooms')->required()->numeric()->label('Number of Rooms'),
                                    Select::make('hotel_board_basis')
                                        ->label('Meal Plans Available')
                                        ->multiple()
                                        ->options(array_combine(MealPlansEnum::values(), MealPlansEnum::values()))
                                        ->required(),
                                    TextInput::make('product.website')->url()->label('Website')->maxLength(191),
                                    FileUpload::make('product.hero_image')
                                        ->image()
                                        ->imageEditor()
                                        ->preserveFilenames()
                                        ->directory('products')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->columnSpan(1)
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $originalPath = $state->storeAs('products', $state->getClientOriginalName(), 'public');
                                                $thumbnailPath = 'products/thumbnails/' . $state->getClientOriginalName();
                                                if (Storage::disk('public')->exists($originalPath)) {
                                                    $image = Image::read(Storage::disk('public')->get($originalPath));
                                                    $image->resize(150, 150);
                                                    Storage::disk('public')->put($thumbnailPath, (string) $image->encode());
                                                    $set('product.hero_image_thumbnails', $thumbnailPath);
                                                }
                                            }
                                        }),
                                    Hidden::make('product.hero_image_thumbnails')->dehydrated(),
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
                                            ->label('Get Location by Address')
                                            ->placeholder(fn($get) => $get('addressArr.line_1') . ' ' . $get('addressArr.city')),
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
                                        ->label('City')->readOnly(),
                                    TextInput::make('addressArr.line_1')
                                        ->label('Line 1')->readOnly(),
                                    TextInput::make('addressArr.country_code')
                                        ->label('Country Code')->readOnly(),
                                    TextInput::make('addressArr.state_province_name')
                                        ->label('State Province Name')->readOnly(),
                                ]),
                        ])
                        ->columns(1),

                    // Tab 3: Data Sources
                    CustomTab::make('Data Sources')
                        ->id('data-sources')
                        ->schema([
                            Select::make('product.content_source_id')
                                ->label('Content Source')
                                ->options(ContentSource::pluck('name', 'id'))
                                ->required(),
                            Select::make('room_images_source_id')
                                ->label('Room Images Source')
                                ->placeholder('Select an option')
                                ->options(ContentSource::pluck('name', 'id'))
                                ->required(),
                            Select::make('product.property_images_source_id')
                                ->label('Property Images Source')
                                ->options(ContentSource::pluck('name', 'id'))
                                ->required(),
                            Select::make('product.default_currency')
                                ->label('Default Currency')
                                ->required()
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
                            TextInput::make('weight')->label('Weight')
                                ->integer(),
                            TextInput::make('travel_agent_commission')
                                ->label('Travel Agent Commission')
                                ->numeric('decimal'),
                            Select::make('channels')
                                ->label('Channels')
                                ->multiple()
                                ->options(Channel::pluck('name', 'id')),
                            Select::make('galleries')
                                ->label('Galleries')
                                ->multiple()
                                ->options(ImageGallery::pluck('gallery_name', 'id')),

                        ])
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

    public static function getCoreFields($record = null): array
    {
        return [
            Select::make('giata_code')
                ->label('GIATA code')
                ->searchable()
                ->getSearchResultsUsing(function (string $search): ?array {
                    $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                    $result = Property::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                        ->whereRaw("MATCH(name) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                        ->orWhere('code', 'like', "%$search%")
                        ->limit(100);
                    return $result->pluck('full_name', 'code')
                        ->mapWithKeys(function ($full_name, $code) {
                            return [$code => $code . ' (' . $full_name  . ')'];
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
//                ->disabled(fn () => $record)
                ->options(function () {
                    $query = Vendor::query();
                    if (auth()->user()->hasRole(RoleSlug::EXTERNAL_USER->value)) {
                        dump(auth()->user()->currentTeam->name);
                        $query->where('name', auth()->user()->currentTeam->name);
                    }
                    return $query->pluck('name', 'id')->toArray();
                })
                ->dehydrated()
                ->required(),
        ];
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $data['address'] = $data['addressArr'];

        $hotel = Hotel::create(Arr::only($data, [
            'weight',
            'giata_code',
            'featured_flag',
            'sale_type',
            'address',
            'star_rating',
            'num_rooms',
            'room_images_source_id',
            'hotel_board_basis',
            'travel_agent_commission'
        ]));

        $data['product']['product_type'] = 'hotel';
        $data['product']['verified'] = false;

        $product = $hotel->product()->create(Arr::only($data['product'], [
            'vendor_id',
            'hero_image',
            'hero_image_thumbnails',
            'product_type',
            'name',
            'verified',
            'lat',
            'lng',
            'content_source_id',
            'property_images_source_id',
            'default_currency',
            'website'
        ]));

        if (isset($data['galleries'])) {
            $hotel->product->galleries()->sync($data['galleries']);
        }

        if (isset($data['channels'])) {
            $hotel->product->channels()->sync($data['channels']);
        }

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-repository.edit', $hotel);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        if (!isset($data['product']['verified'])) {
            $data['verified'] = false;
        }

        $data['address'] = $data['addressArr'];

        $hotel = Hotel::find($this->record->id);

        $productData = Arr::only($data['product'], [
            'vendor_id',
            'name',
            'verified',
            'hero_image',
            'hero_image_thumbnails',
            'lat',
            'lng',
            'content_source_id',
            'property_images_source_id',
            'default_currency',
            'website'
        ]);

        $hotel->product->update($productData);

        $hotel->update(Arr::only($data, [
            'weight',
            'giata_code',
            'featured_flag',
            'sale_type',
            'address',
            'star_rating',
            'num_rooms',
            'room_images_source_id',
            'hotel_board_basis',
            'travel_agent_commission'
        ]));

        if (isset($data['galleries'])) {
            $hotel->product->galleries()->sync($data['galleries']);
        }

        if (isset($data['channels'])) {
            $hotel->product->channels()->sync($data['channels']);
        }

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-repository.edit', $hotel);
    }

    protected function handleReverseGeocoding(array $state, callable $set): void
    {
        if (isset($state['lat']) && isset($state['lng'])) {
            $set('product.lat', $state['lat']);
            $set('product.lng', $state['lng']);

            // Reverse geocoding logic
            $apiKey = config('filament-google-maps.key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$state['lat']},{$state['lng']}&key={$apiKey}";

            $response = file_get_contents($url);
            $results = json_decode($response, true);
            $streetNumber = $route = $city = $postal_town = $state_province_name = $zip = $country_code = '';

            if (!empty($results['results'][0]['address_components'])) {
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

                $set('addressArr.line_1', trim("$streetNumber $route, $zip"));
                $set('addressArr.city', $city !== '' ? $city : $postal_town);
                $set('addressArr.state_province_name', $state_province_name);
                $set('addressArr.country_code', $country_code);
            }
        }
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-form');
    }
}
