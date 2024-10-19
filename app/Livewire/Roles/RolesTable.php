<?php

namespace App\Livewire\Roles;

use App\Models\Role;
use Filament\Tables\Actions\CreateAction;;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class RolesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Role::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (Role $record): string => route('roles.edit', $record))
                        ->visible(fn (Role $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (Role $record) => $record->delete())
                        ->visible(fn (Role $record) => Gate::allows('delete', $record)),
                ])->hidden(fn (Role $record) => $record->slug == 'admin'),
            ])->headerActions([
                CreateAction::make()
                    ->label('Create')
                    ->url(fn (): string => route('roles.create'))
                    ->visible(fn () => Gate::allows('create', Role::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.roles.roles-table');
    }
}
