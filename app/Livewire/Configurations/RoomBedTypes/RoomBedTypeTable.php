<?php

namespace App\Livewire\Configurations\RoomBedTypes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigRoomBedType;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class RoomBedTypeTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigRoomBedType::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigRoomBedType $record): string => route('configurations.room-bed-types.edit', $record))
                        ->visible(fn (ConfigRoomBedType $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigRoomBedType $record) => $record->delete())
                        ->visible(fn (ConfigRoomBedType $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.room-bed-types.create'))
                    ->visible(fn () => Gate::allows('create', ConfigRoomBedType::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.room_bed_types.room-bed-type-table');
    }
}
