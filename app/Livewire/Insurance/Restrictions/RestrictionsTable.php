<?php

namespace App\Livewire\Insurance\Restrictions;

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
use Modules\Insurance\Models\InsuranceRestriction;

class RestrictionsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(InsuranceRestriction::query())
            ->columns([
                TextColumn::make('insurance_plan_id')
                    ->label('Insurance Plan ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('provider.name')
                    ->label('Provider name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('restrictionType.name')
                    ->label('Restriction type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('compare')
                    ->label('Compare sign')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('value')
                    ->label('Restriction value')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn(InsuranceRestriction $record): string => route('insurance-restrictions.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(InsuranceRestriction $record) => $record->delete()),
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
        return view('livewire.insurance.restrictions.restrictions-table');
    }
}
