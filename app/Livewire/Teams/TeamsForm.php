<?php

namespace App\Livewire\Teams;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\InviteTeamMember;
use App\Helpers\ClassHelper;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Jetstream\Actions\UpdateTeamMemberRole;
use Laravel\Jetstream\Jetstream;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class TeamsForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    public Team $record;

    public array $teamRoles = [];

    public function mount(Team $team): void
    {
        $this->record = $team;

        $this->form->fill($this->record->attributesToArray());

        $this->teamRoles = collect(Jetstream::$roles)->values()->pluck('name', 'key')->all();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191)
                    ->disabled(),
                TextInput::make('owner')
                    ->required()
                    ->maxLength(191)
                    ->formatStateUsing(fn (Team $team) => $team->owner?->name)
                    ->disabled(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(
                User::query()
                    ->with(['teams' => fn ($q) => $q->where('team_id', $this->record->id)])
                    ->whereIn('id', $this->record->users->pluck('id'))
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->state(fn (User $record) => $this->teamRoles[$record->teams->first()?->membership?->role] ?? ''),
            ])
            ->actions([
                EditAction::make('edit-role')
                    ->modalHeading('Edit Role')
                    ->iconButton()
                    ->form([
                        Select::make('role')
                            ->options($this->teamRoles)
                            ->formatStateUsing(fn (User $record) => $record->teams->first()?->membership?->role)
                            ->required(),
                    ])->action(function (User $record, $data) {
                        resolve(UpdateTeamMemberRole::class)
                            ->update($this->record->owner, $this->record, $record->id, $data['role']);

                        Notification::make()
                            ->title('Success updated role')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make('delete')
                    ->iconButton()
                    ->action(function (User $record) {
                        $this->record->removeUser($record);
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form([
                        Select::make('email')
                            ->searchable()
                            ->options(User::whereDoesntHave(
                                'teams',
                                fn ($query) => $query->where('team_id', $this->record->id)
                            )->pluck('email', 'email'))
                            ->required(),
                        Select::make('role')
                            ->options($this->teamRoles)
                            ->required(),
                    ])
                    ->action(function ($data) {
                        if (config('packages.jetstream.use-invite')) {
                            resolve(InviteTeamMember::class)
                                ->invite($this->record->owner, $this->record, $data['email'], $data['role']);
                            $message = 'Success invitation to email '.$data['email'];
                        } else {
                            resolve(AddTeamMember::class)
                                ->add($this->record->owner, $this->record, $data['email'], $data['role']);
                            $user = User::whereEmail($data['email'])->first();
                            $user->switchTeam($this->record);

                            $message = 'Success adding team member';
                        }

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $exists = $this->record->exists;
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('teams.index');
    }

    public function render(): View
    {
        return view('livewire.teams.teams-form');
    }
}
