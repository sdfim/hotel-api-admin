<?php

namespace App\Livewire;

use App\Models\Reservation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\ImageColumn;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Reservation::query()->whereNull('canceled_at')->orderBy('created_at', 'DESC'))
            ->columns([
                TextColumn::make('reservation_contains')
                ->state(function (Model $record) {
                    $field = json_decode($record->reservation_contains, true);
                    $content = '';
                    if (is_array($field)) {
                        foreach ($field as $key => $value) {
                            if ($key == 'booking_id') {
                                $content .= $key . ': ' . $value ;
                            }
                        }
                    }
                    return $content;
                })
                ->tooltip(function (Model $record){
                    $field = json_decode($record->reservation_contains, true);
                    $tooltip = '';
                    if (is_array($field)) {
                        foreach ($field as $key => $value) {
                            $tooltip .= $key . ': ' . $value . "<br>";
                        }
                    }
                    return $tooltip;
                }),
                // ImageColumn::make('reservation_contains.hotel_images')
                // ->state(function (Model $record) {
                //     $field = json_decode($record->reservation_contains, true);
                //     if(isset($field['hotel_images'])){
                //         return json_decode($field['hotel_images']);
                //     }else{
                //         return '';
                //     }
                // })
                // ->circular()
                // ->stacked(),
                ViewColumn::make('reservation_contains.hotel_images')->view('dashboard.reservations.column.hotel-images'),
                TextColumn::make('channel.name')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('date_offload')
                    ->default('N\A')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('date_travel')
                    ->dateTime()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('passenger_surname')
                    ->searchable(isIndividual: true),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('canceled_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(Reservation $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
                            $record->update(['canceled_at' => date('Y-m-d H:i:s')]);
                        })
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                ])->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.reservations-table');
    }
}
