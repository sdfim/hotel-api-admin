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
use Modules\HotelContentRepository\Models\ImageGallery;

class HotelForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Hotel $record;

    public function mount(Hotel $hotel): void
    {
        $this->record = $hotel;

        $data = $this->record->toArray();

        $data['address'] = $this->record->address ? json_encode($this->record->address) : '';
        $data['location'] = $this->record->location ? json_encode($this->record->location) : '';
        $data['galleries'] = $this->record->galleries->pluck('id')->toArray();

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
                            TextInput::make('type')->required()->maxLength(191),
                            Textarea::make('address')->required(),
                            TextInput::make('location')->required(),
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

                    // Tab 3
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
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
//        $data['location'] = [
//            'latitude' => $data['latitude'],
//            'longitude' => $data['longitude'],
//        ];
        $this->record->update(Arr::only($data, [
            'name', 'location', 'type', 'verified', 'direct_connection', 'manual_contract', 'commission_tracking', 'address', 'star_rating', 'website', 'num_rooms', 'featured', 'content_source_id', 'room_images_source_id', 'property_images_source_id', 'channel_management', 'hotel_board_basis', 'default_currency'
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
