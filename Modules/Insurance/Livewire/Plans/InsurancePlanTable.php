<?php

namespace Modules\Insurance\Livewire\Plans;

use App\Models\Enums\RoleSlug;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Insurance\Models\InsurancePlan;

class InsurancePlanTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsurancePlan::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id),
                    )
            )
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('booking_item')
                    ->label('Booking Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_insurance_cost')
                    ->label('Total Insurance Cost')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('insurance_vendor_fee')
                    ->label('Insurance Vendor Fee')
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
