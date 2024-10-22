<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
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

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Hotel $record;

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel;

        $this->form->fill([
            'name' => $this->record->name,
            'type' => $this->record->type,
            'verified' => $this->record->verified,
            'direct_connection' => $this->record->direct_connection,
            'manual_contract' => $this->record->manual_contract,
            'commission_tracking' => $this->record->commission_tracking,
            'address' => $this->record->address ? json_encode($this->record->address) : '',
            'star_rating' => $this->record->star_rating,
            'website' => $this->record->website,
            'num_rooms' => $this->record->num_rooms,
            'featured' => $this->record->featured,
            'location' => $this->record->location ? json_encode($this->record->location) : '',
            'content_source_id' => $this->record->content_source_id,
            'room_images_source_id' => $this->record->room_images_source_id,
            'property_images_source_id' => $this->record->property_images_source_id,
            'channel_management' => $this->record->channel_management,
            'hotel_board_basis' => $this->record->hotel_board_basis,
            'default_currency' => $this->record->default_currency,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Hotel Details')
                ->columns(1)
                ->tabs([
                    // Tab 1
                    Tabs\Tab::make('General Information')
                        ->schema([
                            TextInput::make('name')->required()->maxLength(191),
                            TextInput::make('type')->required()->maxLength(191),
                            Textarea::make('address')->required(),
                            TextInput::make('location')->required(),
                        ])
                        ->columns(2),

                    // Tab 2
                    Tabs\Tab::make('Verification & Sources')
                        ->schema([
                            Select::make('content_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                            Select::make('room_images_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                            Select::make('property_images_source_id')->options(ContentSource::pluck('name', 'id'))->required(),
                            Checkbox::make('verified'),
                            Checkbox::make('direct_connection'),
                            Checkbox::make('manual_contract'),
                            Checkbox::make('commission_tracking'),
                            Checkbox::make('featured'),
                        ])
                        ->columns(3),

                    // Tab 4
                    Tabs\Tab::make('Details & Management')
                        ->schema([
                            TextInput::make('star_rating')->required()->numeric(),
                            TextInput::make('website')->url()->maxLength(191),
                            TextInput::make('num_rooms')->required()->numeric(),
                            TextInput::make('channel_management')->required(),
                            TextInput::make('hotel_board_basis'),
                            TextInput::make('default_currency')->required()->maxLength(3),
                        ])
                        ->columns(2),
                ])
            ])
            ->statePath('data')
            ->model($this->record)
            ->columns(1);
    }

    public static function schemeForm(): array
    {
        return [
            TextInput::make('name')->label('Name')->required(),
            TextInput::make('type')->label('Type')->required(),
            TextInput::make('address')->label('Address')->required(),
            TextInput::make('location')->label('Location')->required(),
            Select::make('content_source_id')->label('Content Source')->options(ContentSource::pluck('name', 'id'))->required(),
            Select::make('room_images_source_id')->label('Room Images Source')->options(ContentSource::pluck('name', 'id'))->required(),
            Select::make('property_images_source_id')->label('Property Images Source')->options(ContentSource::pluck('name', 'id'))->required(),
            Checkbox::make('verified')->label('Verified'),
            Checkbox::make('direct_connection')->label('Direct Connection'),
            Checkbox::make('manual_contract')->label('Manual Contract'),
            Checkbox::make('commission_tracking')->label('Commission Tracking'),
            Checkbox::make('featured')->label('Featured'),
            TextInput::make('star_rating')->label('Star Rating')->required(),
            TextInput::make('website')->label('Website')->url()->required(),
            TextInput::make('num_rooms')->label('Number of Rooms')->required(),
            TextInput::make('channel_management')->label('Channel Management')->required(),
            TextInput::make('hotel_board_basis')->label('Hotel Board Basis'),
            TextInput::make('default_currency')->label('Default Currency')->required(),
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $data['location'] = [
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ];
        $this->record->update(Arr::only($data, [
            'name', 'type', 'verified', 'direct_connection', 'manual_contract', 'commission_tracking', 'address', 'star_rating', 'website', 'num_rooms', 'featured', 'content_source_id', 'room_images_source_id', 'property_images_source_id', 'channel_management', 'hotel_board_basis', 'default_currency'
        ]));

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('hotels.index');
    }

    public function render()
    {
        return view('livewire.hotels.hotel-form');
    }
}
