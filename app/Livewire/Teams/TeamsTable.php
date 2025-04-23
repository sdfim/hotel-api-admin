<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class TeamsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Team::query()->whereIn('id', Auth::user()->allTeams()->pluck('id')))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (Team $record): string => route('teams.edit', $record))
                        ->visible(fn (Team $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (Team $record) => $record->delete())
                        ->visible(fn (Team $record) => Gate::allows('delete', $record)),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.teams.teams-table');
    }
}
