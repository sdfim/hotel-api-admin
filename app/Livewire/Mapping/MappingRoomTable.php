<?php

namespace App\Livewire\Mapping;

use App\Helpers\Strings;
use App\Jobs\FetchMappingRoomJob;
use App\Models\MappingRoom;
use App\Models\Property;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MappingRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(MappingRoom::query())
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('giata_id')->label('GIATA ID')->searchable(),
                TextColumn::make('supplier')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->supplier === 'HBSI',
                        'primary' => fn ($record) => $record->supplier !== 'HBSI',
                    ]),
                TextColumn::make('supplier_room_code')->label('Supplier Code')->searchable(),
                TextColumn::make('unified_room_code')->label('Unified Code')->searchable(),
                TextColumn::make('supplier_room_name')->label('Supplier Room Name')->searchable()->wrap(),
                TextColumn::make('match_percentage')->label('Match %')->sortable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->filters([])
            ->actions([
                EditAction::make()
                    ->form(self::getCoreFields())
                    ->action(function (MappingRoom $record, array $data) {
                        $record->update($data);
                        Notification::make()
                            ->title('Success')
                            ->body('Mapping Room updated successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->action(fn (MappingRoom $record) => $record->delete())
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form(self::getCoreFields())
                    ->action(function (array $data) {
                        MappingRoom::create($data);
                        Notification::make()
                            ->title('Success')
                            ->body('Mapping Room created successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('selectGiata')
                    ->label('AI Assistant')
                    ->form([
                        Select::make('giata_id')
                            ->label('GIATA code')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): ?array {
                                $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                                $result = Property::select(
                                    DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                                    ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                                    ->limit(100);

                                return $result->pluck('full_name', 'code')
                                    ->mapWithKeys(function ($full_name, $code) {
                                        return [$code => $code.' ('.$full_name.')'];
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
                            ->required(),
                    ])
                    ->modalHeading('Room mapping for content and pricing providers')
                    ->action(function (array $data) {
                        $this->handleGiataCodeSelection($data);
                    }),
            ]);
    }

    public static function getCoreFields($record = null): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('giata_id')
                    ->label('GIATA code')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): ?array {
                        $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                        $result = Property::select(
                            DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                            ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                            ->limit(100);

                        return $result->pluck('full_name', 'code')
                            ->mapWithKeys(function ($full_name, $code) {
                                return [$code => $code.' ('.$full_name.')'];
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
                TextInput::make('unified_room_code')->label('Unified Room Code')->required(),
                Select::make('supplier')
                    ->label('Supplier')
                    ->options([
                        'Expedia' => 'Expedia',
                        'HBSI' => 'HBSI',
                        'IcePortal' => 'IcePortal',
                    ])
                    ->required(),
                TextInput::make('supplier_room_code')->label('Supplier Room Code')->required(),
                TextInput::make('supplier_room_name')->label('Supplier Room Name'),
                TextInput::make('match_percentage')->label('Match %')->numeric()->default(100),
            ]),
        ];
    }

    protected function handleGiataCodeSelection(array $data): void
    {
        FetchMappingRoomJob::dispatch($data['giata_id']);

        Notification::make()
            ->title('Rooms Imported')
            ->body('Operation queued successfully. It will be executed within approximately 10 sec.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.mapping-room-table');
    }
}
