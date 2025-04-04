<?php

namespace App\Livewire\Users;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class UsersTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(User::query())
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'))
            ->columns([

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('roles.0.name')
                    ->label('Role'),

                TextColumn::make('allTeams')
                    ->label('Can View Vendors (Teams)')
                    ->wrap()
                    ->getStateUsing(fn (User $record) => $record->hasRole(RoleSlug::EXTERNAL_USER->value) ? $record->allTeams()->pluck('name')->join(', ') : 'All'),

                TextColumn::make('currentTeam.name')
                    ->label('Vendor (Current Team)'),

                IconColumn::make('owner_team')
                    ->label('Owner')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => $record->currentTeam?->owner()->is($record)),

            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (User $record): string => route('users.edit', $record))
                        ->visible(fn (User $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            // Check if the user is the owner of the team
                            $teams = Team::where('user_id', $record->id)->get();
                            foreach ($teams as $team) {
                                if ($team->owner()->is($record)) {
                                    $newOwner = $record->currentTeam->allUsers()->where('id', '!=', $record->id)->first();
                                    if ($newOwner) {
                                        $team->owner()->associate($newOwner);
                                        $team->save();
                                    }
                                }

                            }
                            if ($record->currentTeam) {
                                $record->currentTeam->users()->detach($record->id);
                            }
                            // Check if the team has no users left
                            if ($record->currentTeam?->allUsers()?->count() === 0) { // Check if the team has no users left
                                $record->currentTeam()->delete();
                            }
                            $record->delete();
                        })
                        ->visible(fn (User $record) => Gate::allows('delete', $record)),
                ]),
            ])->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('users.create'))
                    ->visible(fn () => Gate::allows('create', User::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.users.users-table');
    }
}
