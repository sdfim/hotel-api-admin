<?php

namespace App\Livewire\Configurations\InsuranceDocumentationTypes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigInsuranceDocumentationType;
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

class InsuranceDocumentationTypesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigInsuranceDocumentationType::query())
            ->columns([
                TextColumn::make('name_type')
                    ->label('Name Type')
                    ->searchable(),
                TextColumn::make('viewable')
                    ->label('Viewable')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (ConfigInsuranceDocumentationType $record): string => route('configurations.insurance-documentation-types.edit', $record))
                    ->visible(fn (ConfigInsuranceDocumentationType $record) => Gate::allows('update', $record)),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ConfigInsuranceDocumentationType::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('delete', ConfigInsuranceDocumentationType::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.insurance-documentation-types.create'))
                    ->visible(fn () => Gate::allows('create', ConfigInsuranceDocumentationType::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.insurance-documentation-types.insurance-documentation-types-table');
    }
}
