<?php

namespace App\Livewire\Configurations\DescriptiveTypes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigDescriptiveType;
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
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Livewire\Component;

class DescriptiveTypesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigDescriptiveType::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
                TextColumn::make('type'),
                TextColumn::make('location'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigDescriptiveType $record): string => route('configurations.descriptive-types.edit', $record))
                        ->visible(fn (ConfigDescriptiveType $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigDescriptiveType $record) => $record->delete())
                        ->visible(fn (ConfigDescriptiveType $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.descriptive-types.create'))
                    ->visible(fn () => Gate::allows('create', ConfigDescriptiveType::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.descriptive-types.descriptive-types-table');
    }
}
