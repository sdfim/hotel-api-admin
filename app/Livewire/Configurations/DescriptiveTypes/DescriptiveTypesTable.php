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
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

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
                        ->modalHeading('Are you sure you want to delete?')
                        ->modalDescription(function ($record) {
                            $warnings = [];
                            
                            $contentSections = ProductDescriptiveContentSection::where('descriptive_type_id', $record->id)
                                ->with('product')
                                ->get();

                            $productNames = $contentSections->pluck('product.name')
                                ->filter()
                                ->unique()
                                ->toArray();
                            
                            if (count($productNames) > 0) {
                                $warnings[] = 'Descriptive Type "' . $record->name . '" is used in content sections by products: ' . implode(', ', $productNames);
                            }
                            
                            if (count($warnings) > 0) {
                                return "Warning:\n" . implode("\n", $warnings);
                            }
                            return 'This action will permanently delete the selected descriptive type.';
                        })
                        ->modalSubmitActionLabel('Delete')
                        ->modalCancelActionLabel('Cancel')
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
