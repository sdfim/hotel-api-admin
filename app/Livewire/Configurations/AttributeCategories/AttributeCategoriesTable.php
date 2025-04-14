<?php

namespace App\Livewire\Configurations\AttributeCategories;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttributeCategory;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class AttributeCategoriesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigAttributeCategory::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (ConfigAttributeCategory $record): string => route('configurations.attribute-categories.edit', $record))
                    ->visible(fn (ConfigAttributeCategory $record) => Gate::allows('update', $record)),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ConfigAttributeCategory::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('delete', ConfigAttributeCategory::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.attribute-categories.create'))
                    ->visible(fn () => Gate::allows('create', ConfigAttributeCategory::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.attribute-categories.attribute-categories-table');
    }
}
