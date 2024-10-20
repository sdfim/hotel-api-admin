<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class RolesForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    public Role $record;

    public function mount(Role $role): void
    {
        $this->record = $role;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->live(true)
                    ->required()
                    ->maxLength(191)
                    ->afterStateUpdated(
                        fn ($state, Set $set) => !$this->record->exists ? $set('slug', Str::slug($state)) : $state
                    ),
                TextInput::make('slug')
                    ->unique(ignorable: $this->record)
                    ->required()
                    ->maxLength(191)
                    ->disabled($this->record->exists),
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
                        'roles',
                        fn ($query) => $query->where('role_id', $this->record->id)
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
                                'roles',
                                fn ($query) => $query->where('role_id', $this->record->id)
                            )->pluck('name', 'id'))
                    ])
                    ->action(fn ($data) => $this->record->permissions()->attach($data['id'])),
            ]);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('roles.index');
    }

    public function render(): View
    {
        return view('livewire.roles.roles-form');
    }
}
