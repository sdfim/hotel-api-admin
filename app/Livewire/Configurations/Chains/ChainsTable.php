<?php

namespace App\Livewire\Configurations\Chains;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigChain;
use App\Models\Property;
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

class ChainsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigChain::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigChain $record): string => route('configurations.chains.edit', $record))
                        ->visible(fn (ConfigChain $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Are you sure you want to delete?')
                        ->modalDescription(function ($record) {
                            $warnings = [];
                            
                            $properties = Property::whereJsonContains('chain', $record->name)
                                ->get();
                            
                            $propertyNames = $properties->pluck('name')
                                ->filter()
                                ->unique()
                                ->toArray();
                            
                            if (count($propertyNames) > 0) {
                                $warnings[] = 'Chain "' . $record->name . '" is used by properties: ' . implode(', ', $propertyNames);
                            }
                            
                            if (count($warnings) > 0) {
                                return "Warning:\n" . implode("\n", $warnings);
                            }
                            return 'This action will permanently delete the selected chain.';
                        })
                        ->modalSubmitActionLabel('Delete')
                        ->modalCancelActionLabel('Cancel')
                        ->action(fn (ConfigChain $record) => $record->delete())
                        ->visible(fn (ConfigChain $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.chains.create'))
                    ->visible(fn () => Gate::allows('create', ConfigChain::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.chains.chains-table');
    }
}
