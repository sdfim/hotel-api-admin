<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Models\Configurations\ConfigJobDescription;
use App\Models\Property;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use Modules\Enums\HotelTypeEnum;
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

        $this->verified = $hotel->product->verified;

        $data = $this->record->toArray();

        foreach ($this->record->address as $key => $value) {
            $data['addressArr'][$key] = $value;
        }

        $data['galleries'] = $this->record->product->galleries->pluck('id')->toArray();

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
        return [
            Tabs::make('Hotel Details')
                ->columns(1)
                ->tabs([
                    // Tab 1: Product
                    CustomTab::make('Product')
                        ->id('product')
                        ->schema([
                            Select::make('product.vendor_id')
                                ->label('Vendor Name')
                                ->options(Vendor::pluck('name', 'id')->toArray())
                                ->disabled(fn () => $this->record->exists)
                                ->dehydrated()
                                ->required(),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('product.name')->required()->label('Product Name')->maxLength(191),
                                    Select::make('sale_type')
                                        ->label('Type')
                                        ->options([
                                            HotelTypeEnum::DIRECT_CONNECTION->value => HotelTypeEnum::DIRECT_CONNECTION->value,
                                            HotelTypeEnum::MANUAL_CONTRACT->value => HotelTypeEnum::MANUAL_CONTRACT->value,
                                            HotelTypeEnum::COMMISSION_TRACKING->value => HotelTypeEnum::COMMISSION_TRACKING->value,
                                        ])->required(),
                                    TextInput::make('star_rating')->required()->numeric()->label('Star Rating'),
                                    TextInput::make('num_rooms')->required()->numeric()->label('Number of Rooms'),
                                    TextInput::make('hotel_board_basis')->label('Hotel Board Basis'),
                                    TextInput::make('product.website')->url()->label('Website')->maxLength(191),
                                ])
                        ])
                        ->columns(1),

                    // Tab 2: Location
                    CustomTab::make('Location')
                        ->id('location')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('addressArr.city')
                                        ->label('City'),
                                    TextInput::make('addressArr.line_1')
                                        ->label('Line 1'),
                                    TextInput::make('addressArr.postal_code')
                                        ->label('Postal Code'),
                                    TextInput::make('addressArr.country_code')
                                        ->label('Country Code'),
                                    TextInput::make('addressArr.state_province_code')
                                        ->label('State Province Code'),
                                    TextInput::make('addressArr.state_province_name')
                                        ->label('State Province Name'),
                                    Checkbox::make('addressArr.obfuscation_required')
                                        ->label('Obfuscation Required'),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                        TextInput::make('full_address')
                                            ->label('Get location by address'),
                                        TextInput::make('product.lat')->label('Latitude')->required()->numeric(),
                                        TextInput::make('product.lng')->label('Longitude')->required()->numeric(),
                                    ])->columnSpan(1),
                                    Map::make('product.location')
                                        ->label('')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $set('product.lat', $state['lat']);
                                            $set('product.lng', $state['lng']);
                                        })
                                        ->height(fn () => '170px')
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
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->columns(1),

                    // Tab 3: Data Sources
                    CustomTab::make('Data Sources')
                        ->id('data-sources')
                        ->schema([
                            Select::make('product.content_source_id')->label('Content Source')->options(ContentSource::pluck('name', 'id'))->required(),
                            Select::make('room_images_source_id')->label('Room Images Source')->options(ContentSource::pluck('name', 'id'))->required(),
                            Select::make('product.property_images_source_id')->label('Property Images Source')->options(ContentSource::pluck('name', 'id'))->required(),
                            TextInput::make('travel_agent_commission')->label('Travel Agent Commission')->numeric('decimal')->required(),
                            TextInput::make('product.default_currency')->label('Default Currency')->required()->maxLength(3),
                            TextInput::make('weight')->label('Weight')->integer(),
                        ])
                        ->columns(2),
                ]),
            Actions::make([
                Action::make('save')
                    ->label(strtoupper($this->record->exists ? 'Update Changes' : 'Save Changes'))
                    ->action('edit')
                    ->extraAttributes([
                        'class' => 'save-button',
                    ]),
            ]),
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        if (!isset($data['product']['verified'])) {
            $data['verified'] = false;
        }

        $data['address'] = $data['addressArr'];

        $hotel = Hotel::find($this->record->id);

        $hotel->product->update(Arr::only($data['product'], [
            'name',
            'verified',
            'lat',
            'lng',
            'content_source_id',
            'property_images_source_id',
            'default_currency',
            'website'
        ]));

        $hotel->update(Arr::only($data, [
            'weight',
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

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-repository.index');
    }

    public function render()
    {
        return view('livewire.hotels.hotel-form');
    }
}
