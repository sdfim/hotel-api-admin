<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Hotel $record;

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel;

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
        $data['jobDescriptions'] = $this->record->jobDescriptions->pluck('id')->toArray();

        $this->form->fill($data);
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
                                })->columns(2),
                            CustomRepeater::make('location')
                                ->schema([
                                    Select::make('field')
                                        ->label('')
                                        ->options([
                                        'latitude' => 'Latitude',
                                        'longitude' => 'Longitude',
                                    ])->required(),
                                    TextInput::make('value')
                                        ->label('')
                                        ->numeric('decimal'),
                                ])
                                ->defaultItems(1)
                                ->required()
                                ->afterStateHydrated(function ($component, $state) {
                                    $component->state($state ?? []);
                                })
                                ->beforeStateDehydrated(function ($state) {
                                    return json_encode($state);
                                })
                                ->columns(2),
                            Select::make('galleries')
                                ->label('Galleries')
                                ->multiple()
                                ->options(function () {
                                    return isset($this->record)
                                        ? ImageGallery::hasHotel($this->record->id)->pluck('gallery_name', 'id')
                                        : ImageGallery::pluck('gallery_name', 'id');
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
                        ])
                        ->columns(2),

                    // Tab 3
                    Tabs\Tab::make('Verification ')
                        ->schema([
                            Toggle::make('verified')
                                ->label('Verified')
                                ->onColor('success')
                                ->offColor('danger')
                                ->required(),
                        ]),
                ])
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $data['address'] = array_reduce($data['address'], function ($result, $item) {
            $result[$item['field']] = $item['value'];
            return $result;
        }, []);

        $data['location'] = array_reduce($data['location'], function ($result, $item) {
            $result[$item['field']] = $item['value'];
            return $result;
        }, []);

        $hotel = Hotel::find($this->record->id);

        $hotel->update(Arr::only($data, [
            'name', 'location', 'type', 'verified', 'direct_connection', 'manual_contract', 'commission_tracking', 'address', 'star_rating', 'website', 'num_rooms', 'featured', 'content_source_id', 'room_images_source_id', 'property_images_source_id', 'channel_management', 'hotel_board_basis', 'default_currency'
        ]));

        if (isset($data['jobDescriptions'])) {
            $hotel->jobDescriptions()->sync($data['jobDescriptions']);
        }

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
