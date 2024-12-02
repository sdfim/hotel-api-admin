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
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;

class InsurancePlanTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $vendorId;
    public bool $viewAll = false;

    public function mount(?Vendor $vendor, bool $viewAll = false): void
    {
        $this->vendorId = $vendor->id;
        $this->viewAll = $viewAll;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->relationship(name: 'vendor', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    TextInput::make('booking_item')
                        ->label('Booking Item')
                        ->required(),
                    TextInput::make('total_insurance_cost')
                        ->label('Total Insurance Cost')
                        ->numeric()
                        ->required(),
                    TextInput::make('insurance_vendor_fee')
                        ->label('Insurance Vendor Fee')
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
            ->query(fn () => $this->viewAll
                ? InsurancePlan::query()
                : InsurancePlan::query()->where('vendor_id', $this->vendorId))
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
