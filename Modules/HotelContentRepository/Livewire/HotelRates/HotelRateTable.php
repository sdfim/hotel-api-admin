<?php

namespace Modules\HotelContentRepository\Livewire\HotelRates;

use App\Helpers\ClassHelper;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\HotelRate\AddHotelRate;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRateTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public string $title;

    public ?HotelRoom $record = null;

    public function mount(Hotel $hotel, ?HotelRoom $record = null)
    {
        $this->record = $record;
        $this->hotelId = $hotel->id;
        $this->title = 'Hotel Rate for <h4>'.$hotel->product->name.'</h4>';
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(function () {
                $query = HotelRate::query()->with(['hotel']);
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }

                return $query;
            })
            ->columns([

                TextInputColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'max-width: 200px;'])
                    ->rules(['unique:pd_hotel_rates,code,'.($this->record->id ?? 'NULL').',id,hotel_id,'.$this->hotelId]),

                TextColumn::make('name')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->sortable()
                    ->extraAttributes(['style' => 'max-width: 500px;']),

                TextColumn::make('rooms')
                    ->label('Room Names')
                    ->formatStateUsing(function ($state, $record) {
                        $roomNames = $record->rooms->pluck('name')->toArray();

                        return implode('<br>', $roomNames);
                    })
                    ->wrap()
                    ->html()
                    ->extraAttributes(['style' => 'max-height: 150px; overflow-y: auto;']),

                TextColumn::make('dates')
                    ->label('Dates')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) {
                            $state = json_decode("[$state]", true);
                        }

                        return collect($state)->map(function ($date) {
                            $startDate = \Carbon\Carbon::parse($date['start_date'])->format('Y-m-d');
                            $endDate = \Carbon\Carbon::parse($date['end_date'])->format('Y-m-d');

                            return "{$startDate} - {$endDate}";
                        })->implode('<br>');
                    })
                    ->html(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->iconButton()
                    ->tooltip('Edit Rate')
                    ->url(fn ($record) => route('hotel-rates.edit', ['hotel_rate' => $record->id, 'hotelId' => $record->hotel_id]))
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
                Action::make('duplicate')
                    ->label('')
                    ->iconButton()
                    ->icon('heroicon-o-document-duplicate')
                    ->tooltip('Duplicate Rate')
                    ->action(function ($record) {
                        /** @var AddHotelRate $addHotelRate */
                        $addHotelRate = app(AddHotelRate::class);
                        $newRecord = $addHotelRate->duplicate($record);

                        return redirect()->route('hotel-rates.edit', ['hotel_rate' => $newRecord->id, 'hotelId' => $newRecord->hotel_id]);
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(route('hotel-rates.create', ['hotelId' => $this->hotelId]))
                    ->createAnother(false)
                    ->tooltip('Add New Room')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-rate-table');
    }
}
