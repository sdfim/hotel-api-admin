<?php

namespace App\Livewire\Insurance\RateTiers;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Insurance\Models\InsuranceRateTier;

class RateTiersTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(InsuranceRateTier::query())
            ->columns([
                TextColumn::make('min_price')
                    ->label('Min Price')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('max_price')
                    ->label('Max Price')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insurance_rate')
                    ->label('Insurance Rate, %')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn(InsuranceRateTier $record): string => route('insurance-rate-tiers.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(InsuranceRateTier $record) => $record->delete()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.rate-tiers-table');
    }
}
