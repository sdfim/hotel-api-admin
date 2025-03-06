<?php

namespace App\Livewire\Configurations\KeyMappingOwner;

use App\Helpers\ClassHelper;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class KeyMappingOwnerTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(KeyMappingOwner::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (KeyMappingOwner $record): string => route('configurations.external-identifiers.edit', $record))
                    ->visible(fn (KeyMappingOwner $record) => Gate::allows('update', $record)),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => KeyMappingOwner::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('delete', KeyMappingOwner::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.external-identifiers.create'))
                    ->visible(fn () => Gate::allows('create', KeyMappingOwner::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.key-mapping-owners.key-mapping-owner-table');
    }
}
