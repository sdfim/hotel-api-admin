<?php

namespace Modules\HotelContentRepository\Livewire\HotelRates;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
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
            Grid::make(4)
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->maxLength(191)
                        ->columnSpan(1)
                        ->rule('unique:pd_hotel_rates,code,'.($this->record->id ?? 'NULL').',id,hotel_id,'.$this->data['hotel_id']),
                    Textarea::make('name')
                        ->label('Description')
                        ->required()
                        ->columnSpan(3)
                        ->rows(5)
                        ->maxLength(5000),
                    Grid::make(8)
                        ->schema([
                            Select::make('rooms')
                                ->label('Rooms')
                                ->multiple()
                                ->relationship('rooms', 'name')
                                ->searchable()
                                ->required()
                                ->columnSpan(7),
                            Actions::make([
                                Action::make('add_all_rooms')
                                    ->label('Add All Rooms')
                                    ->action(function () {
                                        $this->data['rooms'] = HotelRoom::where('hotel_id', $this->data['hotel_id'])
                                            ->pluck('id')
                                            ->toArray();
                                        $this->form->fill($this->data);
                                    })
                                    ->button()
                                    ->extraAttributes(['class' => 'h-10 text-right'])
                                    ->visible(Gate::allows('create', Hotel::class)),
                            ])
                                ->extraAttributes(['class' => 'flex justify-end']),
                        ])
                        ->columnSpan(4),
                ]),
            Fieldset::make('Date Setting')
                ->schema([
                    CustomRepeater::make('dates')->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('start_date')
                                    ->placeholder('Travel Start Date')
                                    ->label('')
                                    ->required()
                                    ->native(false)
                                    ->time(false)
                                    ->format('Y-m-d')
                                    ->displayFormat('m/d/Y'),
                                DatePicker::make('end_date')
                                    ->placeholder('Travel End Date')
                                    ->label('')
                                    ->required()
                                    ->native(false)
                                    ->time(false)
                                    ->format('Y-m-d')
                                    ->displayFormat('m/d/Y'),
                            ]),
                    ])->columnSpan(2),
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
