<?php

namespace Modules\HotelContentRepository\Livewire\HotelRates;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Actions\HotelRate\UpdateHotelRate;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRateForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public HotelRate $record;

    public ?int $hotelId = null;

    public function mount(HotelRate $hotelRate, ?Hotel $hotel): void
    {
        $this->record = $hotelRate;
        $this->hotelId = $hotel?->id;

        $this->data = $this->record->attributesToArray();
        $this->data['dates'] = $this->record->dates->toArray();
        if ($this->hotelId) {
            $this->data['hotel_id'] = $this->hotelId;
        }
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getSchema($this->data['hotel_id'] ?? null))
            ->statePath('data')
            ->model($this->record);
    }

    public function getSchema(?int $hotelId = null): array
    {
        $schema = [
            TextInput::make('hotel_id')->hidden(fn () => $this->data['hotel_id']),
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(191),
                    TextInput::make('code')
                        ->required()
                        ->maxLength(191)
                        ->rule('unique:pd_hotel_rates,code,'.($this->record->id ?? 'NULL').',id,hotel_id,'.$this->data['hotel_id']),
                    Select::make('room_ids')
                        ->multiple()
                        ->options(function () {
                            return HotelRoom::where('hotel_id', $this->data['hotel_id'])
                                ->limit(50)
                                ->get()
                                ->pluck('full_name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->columnSpan(2),
                ]),
            CustomRepeater::make('dates')->schema([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('')
                            ->required()
                            ->placeholder('Start Date')
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('')
                            ->required()
                            ->placeholder('End Date')
                            ->native(false),
                    ]),
            ]),
        ];

        return $schema;
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $data['hotel_id'] = $this->data['hotel_id'];
        /** @var UpdateHotelRate $updateHotelRate */
        $updateHotelRate = app(UpdateHotelRate::class);
        $updateHotelRate->execute($this->record, $data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-rates.edit', ['hotel_rate' => $this->record]);
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-rate-form');
    }
}
