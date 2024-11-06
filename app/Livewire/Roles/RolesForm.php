<?php

namespace App\Livewire\Roles;

use App\Helpers\ClassHelper;
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
use Filament\Tables\Actions\DeleteAction;
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

    public array $permissionIds = [];

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
                    ->when($this->record->exists, function ($query) {
                        return $query->whereHas('roles', fn ($query) => $query->where('role_id', $this->record->id));
                    })
                    ->when(!$this->record->exists, function ($query) {
                        return $query->whereIn('id', $this->permissionIds);
                    })
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
            ])
            ->actions([
                DeleteAction::make('delete')
                    ->iconButton()
                    ->action(function (Permission $permission) {
                        if ($this->record->exists) {
                            $this->record->permissions()->detach($permission->id);
                        } else {
                            $this->permissionIds = array_filter($this->permissionIds, fn ($id) => $id != $permission->id);
                        }
                    })
            ])
            ->bulkActions([
                DeleteBulkAction::make('delete')
                    ->action(function (Collection $records) {
                        if ($this->record->exists) {
                            $this->record->permissions()->detach($records->pluck('id'));
                        } else {
                            $ids = $records->pluck('id')->all();
                            $this->permissionIds = array_filter($this->permissionIds, fn ($id) => !in_array($id, $ids));
                        }
                    }),
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
                                'roles',
                                fn ($query) => $query->where('role_id', $this->record->id)
                            )->whereNotIn('id', $this->permissionIds)
                                ->pluck('name', 'id'))
                    ])
                    ->action(function ($data) {
                        if ($this->record->exists) {
                            $this->record->permissions()->attach($data['id']);
                        } else {
                            $this->permissionIds = [
                                ...$this->permissionIds,
                                ...$data['id'],
                            ];
                        }
                    }),
            ]);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $exists = $this->record->exists;
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        if (!$exists) {
            $this->record->permissions()->attach($this->permissionIds);
        }

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
