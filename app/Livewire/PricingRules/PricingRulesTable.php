<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRules;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(PricingRules::query())
            ->columns([
                TextColumn::make('suppliers.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('channels.name')
                    ->label('Channel name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('property')
                    ->label('Property code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('giataProperties.name')
                    ->label('Property name')
                    ->toggleable(),
                TextColumn::make('destination')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('travel_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rule_start_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rule_expiration_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('days')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('nights')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rate_code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('room_type')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('total_guests')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('room_guests')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('number_rooms')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('meal_plan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('rating')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('price_type_to_apply')
                    ->toggleable(),
                TextColumn::make('price_value_type_to_apply')
                    ->toggleable(),
                TextColumn::make('price_value_to_apply')
                    ->toggleable(),
                TextColumn::make('price_value_fixed_type_to_apply')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(PricingRules $record): string => route('pricing_rules.show', $record)),
                    EditAction::make()
                        ->url(fn(PricingRules $record): string => route('pricing_rules.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(PricingRules $record) => $record->delete())
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.pricing-rules-table');
    }
}
