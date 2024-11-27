<?php

namespace Modules\Insurance\Livewire\Plans;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;

class InsurancePlanTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    Select::make('provider_id')
                        ->label('Provider')
                        ->relationship(name: 'provider', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    TextInput::make('booking_item')
                        ->label('Booking Item')
                        ->required(),
                    TextInput::make('total_insurance_cost')
                        ->label('Total Insurance Cost')
                        ->numeric()
                        ->required(),
                    TextInput::make('insurance_provider_fee')
                        ->label('Insurance Provider Fee')
                        ->numeric()
                        ->required(),
                    TextInput::make('commission_ujv')
                        ->label('Commission UJV')
                        ->numeric()
                        ->required(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(InsurancePlan::query())
            ->columns([
                TextColumn::make('provider.name')
                    ->label('Provider Name')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('booking_item')
                    ->label('Booking Item')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('total_insurance_cost')
                    ->label('Total Insurance Cost')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('insurance_provider_fee')
                    ->label('Insurance Provider Fee')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('commission_ujv')
                    ->label('Commission UJV')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->actions([
//                EditAction::make()
//                    ->label('')
//                    ->tooltip('Edit Insurance Plan')
//                    ->form(fn() => $this->schemeForm())
//                    ->fillForm(function (InsurancePlan $record) {
//                        return $record->toArray();
//                    })
//                    ->action(function (InsurancePlan $record, array $data) {
//                        $record->update($data);
//
//                        Notification::make()
//                            ->title('Updated successfully')
//                            ->success()
//                            ->send();
//
//                        return $data;
//                    }),
//                DeleteAction::make()
//                    ->label('')
//                    ->tooltip('Delete Insurance Plan')
//                    ->requiresConfirmation()
//                    ->action(function (InsurancePlan $record) {
//                        $record->delete();
//
//                        Notification::make()
//                            ->title('Deleted successfully')
//                            ->success()
//                            ->send();
//                    })
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.plans.insurance-plan-table');
    }
}
