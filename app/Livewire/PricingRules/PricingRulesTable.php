<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRule;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(PricingRule::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('rule_start_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rule_expiration_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('manipulable_price_type')
                    ->toggleable(),
                TextColumn::make('price_value_type')
                    ->toggleable(),
                TextColumn::make('price_value')
                    ->toggleable(),
                TextColumn::make('price_value_target')
                    ->toggleable(),
//                TextColumn::make('rules')
//                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(PricingRule $record): string => route('pricing_rules.show', $record)),
                    EditAction::make()
                        ->url(fn(PricingRule $record): string => route('pricing_rules.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(PricingRule $record) => $record->delete())
                ])
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pricing-rules.pricing-rules-table');
    }
}
