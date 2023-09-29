<?php

namespace App\Livewire;

use App\Models\PricingRules;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(PricingRules::query())
            ->columns([
                TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('property')
                    ->searchable(),
                TextColumn::make('destination')
                    ->searchable(),
                TextColumn::make('travel_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nights')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rate_code')
                    ->searchable(),
                TextColumn::make('room_type')
                    ->searchable(),
                TextColumn::make('total_guests')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('room_guests')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('number_rooms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('meal_plan')
                    ->searchable(),
                TextColumn::make('rating')
                    ->searchable(),
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
                        ->url(fn(PricingRules $record): string => route('pricing_rules.show', $record))
                        ->color('info'),
                    EditAction::make()
                        ->url(fn(PricingRules $record): string => route('pricing_rules.edit', $record))
                        ->color('primary'),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(PricingRules $record) => $record->delete())
                        ->color('danger'),
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
        return view('livewire.pricing-rules-table');
    }
}
