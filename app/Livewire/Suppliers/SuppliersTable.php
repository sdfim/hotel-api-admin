<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class SuppliersTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Supplier::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('product_type')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (Supplier $record): string => route('suppliers.show', $record)),
                    EditAction::make()
                        ->disabled(fn (Supplier $record): bool => in_array(strtolower($record->name), ['expedia', 'hbsi']))
                        ->url(fn (Supplier $record): string => route('suppliers.edit', $record))
                        ->visible(fn (Supplier $record): bool => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->disabled(fn (Supplier $record): bool => in_array(strtolower($record->name), ['expedia', 'hbsi']))
                        ->requiresConfirmation()
                        ->action(fn (Supplier $record) => $record->delete())
                        ->visible(fn (Supplier $record): bool => Gate::allows('delete', $record)),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.suppliers.suppliers-table');
    }
}
