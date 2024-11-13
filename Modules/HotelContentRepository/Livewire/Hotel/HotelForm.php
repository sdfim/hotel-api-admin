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

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public Hotel $record;
    public $verified;

    public function __construct()
    {
        $this->record = new Hotel();
    }

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel;
        $this->verified = $hotel->verified;

        $data = $this->record->toArray();

        $data['address'] = [];
        foreach ($this->record->address as $key => $value) {
            $data['address'][] = [
                'field' => $key,
                'value' => $value
            ];
        }
        $data['location'] = [];
        foreach ($this->record->location as $key => $value) {
            $data['location'][] = [
                'field' => $key,
                'value' => $value
            ];
        }
        $data['galleries'] = $this->record->galleries->pluck('id')->toArray();

        $this->form->fill($data);
    }

    public function toggleVerified()
    {
        $this->verified = !$this->verified;
        $this->record->update(['verified' => $this->verified]);
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

            Toggle::make('verified')
                ->label('Verified')
                ->onColor('success')
                ->offColor('danger')
                ->columnSpan('full')
                ->required()
                ->hidden(),

            Tabs::make('Hotel Details')
                ->columns(1)
                ->tabs([
                    // Tab 1
                    Tabs\Tab::make('General Information')
                        ->schema([
                            TextInput::make('name')->required()->maxLength(191),
                            Select::make('type')
                                ->label('Type')
                                ->options([
                                    'Direct connection' => 'Direct connection',
                                    'Manual contract' => 'Manual contract',
                                    'Commission tracking' => 'Commission tracking',
                                ])->required(),

                            Grid::make()
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('full_address')
                                                ->label('Get location by address')
                                                ->columnSpan(1),

                                            Grid::make()
                                                ->schema([
                                                    TextInput::make('lat')
                                                        ->label('Latitude')
                                                        ->required()
                                                        ->numeric(),
                                                    TextInput::make('lng')
                                                        ->label('Longitude')
                                                        ->required()
                                                        ->numeric(),

                                                ])
                                                ->columns(2)
                                                ->columnSpan(1),

                                            CustomRepeater::make('address')
                                                ->schema([
                                                    Select::make('field')
                                                        ->label('')
                                                        ->options([
                                                            'city' => 'City',
                                                            'line_1' => 'Line 1',
                                                            'postal_code' => 'Postal Code',
                                                            'country_code' => 'Country Code',
                                                            'state_province_code' => 'State Province Code',
                                                            'state_province_name' => 'State Province Name',
                                                            'obfuscation_required' => 'Obfuscation Required',
                                                        ])->required(),
                                                    TextInput::make('value')
                                                        ->label(''),

                                                ])
                                                ->defaultItems(1)
                                                ->required()
                                                ->afterStateHydrated(function ($component, $state) {
                                                    $component->state($state ?? []);
                                                })
                                                ->beforeStateDehydrated(function ($state) {
                                                    return json_encode($state);
                                                })
                                                ->columns(2)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(1)
                                        ->columnSpan(1),

                                    Map::make('location_gm')
                                        ->label('Location')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $set('lat', $state['lat']);
                                            $set('lng', $state['lng']);
                                        })
                                        ->height(fn () => '400px')
                                        ->defaultZoom(17)
                                        ->autocomplete('full_address')
                                        ->autocompleteReverse(true)
                                        ->reverseGeocode([
                                            'street' => '%n %S',
                                            'city' => '%L',
                                            'state' => '%A1',
                                            'zip' => '%z',
                                        ])
                                        ->defaultLocation(fn () => [$this->data['lat'] ?? 39.526610, $this->data['lng'] ?? -107.727261])
                                        ->draggable()
                                        ->clickable(false)
                                        ->geolocate()
                                        ->geolocateLabel('Get Location')
                                        ->geolocateOnLoad(true, false)
                                        ->columnSpan(1),
                                ])
                                ->columns(2),

                            Select::make('galleries')
                                ->label('Galleries')
                                ->multiple()
                                ->options(function () {
                                    return ImageGallery::pluck('gallery_name', 'id');
                                }),
                        ])
                        ->columns(2),

                    // Tab 2
                    Tabs\Tab::make('Sources & Management')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                Select::make('content_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                                Select::make('room_images_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                                Select::make('property_images_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                            ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('default_currency')->required()->maxLength(3),
                                    TextInput::make('star_rating')->required()->numeric(),
                                    TextInput::make('num_rooms')->required()->numeric(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('channel_management')->required(),
                                    TextInput::make('website')->url()->maxLength(191),
                                    TextInput::make('hotel_board_basis'),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('weight')->integer(),
                                ]),
                        ])
                        ->columns(2),
                ]),
//            Actions::make([
//                Actions\Action::make('Fill Location from Property')
//                    ->label('Fill Location from Property')
//                    ->action(function (Get $get, Set $set) {
//                        $keyMappings = $this->record->keyMappings->toArray();
//                        $filteredKeyMappings = array_filter($keyMappings, function ($mapping) {
//                            return $mapping['key_mapping_owner_id'] === 1;
//                        });
//                        $keyIds = array_column($filteredKeyMappings, 'key_id');
//                        $property = Property::find($keyIds)->first();
//                        if ($property) {
//                            $set('location', [
//                                ['field' => 'latitude', 'value' => $property->latitude],
//                                ['field' => 'longitude', 'value' => $property->longitude],
//                            ]);
//                        }
//                    }),
//            ]),
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        if (!isset($data['verified'])) {
            $data['verified'] = false;
        }

        $data['address'] = array_reduce($data['address'], function ($result, $item) {
            $result[$item['field']] = $item['value'];
            return $result;
        }, []);

        if (isset($data['location'])) {
            $data['location'] = array_reduce($data['location'], function ($result, $item) {
                $result[$item['field']] = $item['value'];
                return $result;
            }, []);
        } else {
            $data['location'] = [
                'latitude' => $data['lat'] ?? 0,
                'longitude' => $data['lng'] ?? 0,
            ];
        }

        $hotel = Hotel::find($this->record->id);

        $hotel->update(Arr::only($data, [
            'name',
            'weight',
            'location',
            'type',
            'verified',
            'lat',
            'lng',
            'address',
            'star_rating',
            'website',
            'num_rooms',
            'featured',
            'content_source_id',
            'room_images_source_id',
            'property_images_source_id',
            'channel_management',
            'hotel_board_basis',
            'default_currency'
        ]));

        if (isset($data['galleries'])) {
            $hotel->galleries()->sync($data['galleries']);
        }

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('hotel_repository.index');
    }

    public function render()
    {
        return view('livewire.hotels.hotel-form');
    }
}
