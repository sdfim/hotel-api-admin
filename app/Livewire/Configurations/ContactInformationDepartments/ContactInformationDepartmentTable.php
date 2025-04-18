<?php

namespace App\Livewire\Configurations\ContactInformationDepartments;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigContactInformationDepartment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
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

class ContactInformationDepartmentTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigContactInformationDepartment::query())
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigContactInformationDepartment $record): string => route('configurations.contact-information-departments.edit', $record))
                        ->visible(fn (ConfigContactInformationDepartment $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigContactInformationDepartment $record) => $record->delete())
                        ->visible(fn (ConfigContactInformationDepartment $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.contact-information-departments.create'))
                    ->visible(fn () => Gate::allows('create', ConfigContactInformationDepartment::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.contact-information-departments.contact-information-department-table');
    }
}
