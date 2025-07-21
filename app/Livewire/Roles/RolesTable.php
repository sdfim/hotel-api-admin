<?php

namespace App\Livewire\Roles;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use App\Models\Role;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
                EditAction::make()
                    ->iconButton()
                    ->url(fn (Role $record): string => route('roles.edit', $record))
                    ->visible(fn (Role $record) => Gate::allows('update', $record))
                    ->hidden(fn (Role $record) => $record->slug == RoleSlug::ADMIN->value),
                DeleteAction::make()
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (Role $record) => $record->delete())
                    ->visible(fn (Role $record) => Gate::allows('delete', $record))
                    ->hidden(),
            ])->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('roles.create'))
                    ->visible(fn () => Gate::allows('create', Role::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.roles.roles-table');
    }
}
