<?php

namespace App\Livewire\Users;

use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Helpers\ClassHelper;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Models\Vendor;

class UsersForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    public User $record;

    public function mount(User $user): void
    {
        $this->record = $user;

        $vendorIds = $user->allTeams()->pluck('vendor_id')->toArray() ?? [];

        $this->form->fill([
            ...$this->record->attributesToArray(),
            'role' => $this->record->roles()->first()?->id,
            'vendor_ids' => $vendorIds,
        ]);
    }

    public function form(Form $form): Form
    {
        $additionalFormData = [];

        if (! $this->record->exists) {
            $additionalFormData[] = TextInput::make('password')
                ->required()
                ->password()
                ->revealable()
                ->formatStateUsing(fn () => Str::password(10));
        }

        $externalUserRoleId = Role::where('slug', 'external-user')->first()?->id;

        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('email')
                    ->unique(ignorable: $this->record)
                    ->required()
                    ->email()
                    ->maxLength(191),
                Select::make('role')
                    ->options(Role::pluck('name', 'id'))
                    ->required()
                    ->reactive(),
                Select::make('vendor_ids')
                    ->label('Can View Vendors')
                    ->multiple()
                    ->options(Vendor::whereJsonContains('type', 'hotel')->pluck('name', 'id'))
                    ->native(false)
                    ->required()
                    ->visible(fn ($get) => (int) $get('role') === $externalUserRoleId),
                ...$additionalFormData,
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(
                Permission::query()
                    ->whereHas(
                        'users',
                        fn ($query) => $query->where('user_id', $this->record->id)
                    )
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
            ])
            ->bulkActions([
                DeleteBulkAction::make('delete')
                    ->action(fn (Collection $records) => $this->record
                        ->permissions()
                        ->detach($records->pluck('id'))
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form([
                        Select::make('id')
                            ->multiple()
                            ->searchable()
                            ->options(Permission::whereDoesntHave(
                                'users',
                                fn ($query) => $query->where('user_id', $this->record->id)
                            )->pluck('name', 'id')),
                    ])
                    ->action(fn ($data) => $this->record->permissions()->attach($data['id'])),
            ]);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $exists = $this->record->exists;
        $data = $this->form->getState();
        $this->record->fill(Arr::only($data, ['name', 'email']));

        if (! $exists) {
            $this->record->password = bcrypt($data['password']);
        }

        $this->record->save();
        $this->record->roles()->sync([$data['role']]);

        if ($data['role'] === Role::where('slug', 'external-user')->first()?->id) {
            $this->addUserToGroups($data['vendor_ids'], $this->record);
        }

        Notification::make()
            ->title($exists ? 'Updated successfully' : 'Created successfully')
            ->success()
            ->send();

        return redirect()->route('users.index');
    }

    public function addUserToGroups(array $vendorIds, User $user): void
    {
        $existingTeams = $user->allTeams()->pluck('vendor_id')->toArray();
        $needToAttach = array_diff($vendorIds, $existingTeams);
        $needToDelete = array_diff($existingTeams, $vendorIds);

        foreach ($needToDelete as $vendorId) {
            $team = Team::where('vendor_id', $vendorId)->first();
            if ($team?->owner->id === $user->id && $team->users()->count() === 0) {
                app(DeleteTeam::class)->delete($team);
                $otherTeam = $user->allTeams()->first();
                if ($otherTeam) {
                    $user->switchTeam($otherTeam);
                }
            } elseif ($team?->owner->id === $user->id && $team->users()->count() !== 0) {
                $newOwner = $team->users()->where('user_id', '!=', $user->id)->first();
                if ($newOwner) {
                    $team->owner()->associate($newOwner);
                    $team->save();
                }
                $team->users()->detach($user->id);
                $otherTeam = $user->allTeams()->first();
                if ($otherTeam) {
                    $user->switchTeam($otherTeam);
                }
            } else {
                $team->users()->detach($user->id);
            }
        }

        foreach ($needToAttach as $vendorId) {
            $team = Team::where('vendor_id', $vendorId)->first();
            if ($team?->exists) {
                $team->users()->attach($user, ['role' => 'admin']);
                $user->switchTeam($team);
            } else {
                $team = app(CreateTeam::class)
                    ->create($user, [
                        'name' => Vendor::where('id', $vendorId)->first()->name,
                    ]);
                $team->update(['vendor_id' => $vendorId]);
                $user->switchTeam($team);
            }
        }

        Notification::make()
            ->title('User added to groups successfully')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.users.users-form');
    }
}
