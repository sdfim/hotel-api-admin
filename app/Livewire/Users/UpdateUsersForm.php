<?php

namespace App\Livewire\Users;

use App\Models\Permission;
use App\Models\Role;
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
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdateUsersForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    public User $record;

    public function mount(User $user): void
    {
        $this->record = $user;

        $this->form->fill([
            ...$this->record->attributesToArray(),
            'role' => $this->record->roles()->first()?->id,
        ]);
    }

    public function form(Form $form): Form
    {
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
                    ->label('Add permission')
                    ->form([
                        Select::make('id')
                            ->multiple()
                            ->searchable()
                            ->options(Permission::whereDoesntHave(
                                'users',
                                fn ($query) => $query->where('user_id', $this->record->id)
                            )->pluck('name', 'id'))
                    ])
                    ->action(fn ($data) => $this->record->permissions()->attach($data['id'])),
            ]);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->update(Arr::only($data, ['name', 'email']));
        $this->record->roles()->sync([$data['role']]);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('users.index');
    }

    public function render(): View
    {
        return view('livewire.users.update-users-form');
    }
}
