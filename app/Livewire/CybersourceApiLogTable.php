<?php

namespace App\Livewire;

use App\Models\CybersourceApiLog;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class CybersourceApiLogTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?CybersourceApiLog $selectedLog = null;

    public bool $showModal = false;

    #[Url]
    public ?string $id = null;

    public function viewLog(CybersourceApiLog $log): void
    {
        $this->selectedLog = $log;
        $this->showModal = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(CybersourceApiLog::query()->latest())
            ->modifyQueryUsing(function ($query) {
                if ($this->id) {
                    $query->where('id', $this->id);
                }
            })
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('booking_id')
                    ->sortable()
                    ->searchable(isIndividual: true)->toggleable(),
                TextColumn::make('response')
                    ->label('Amount')
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(function ($record) {
                        $response = $record->response;
                        $payload = $record->payload;
                        $direction = $record->direction;

                        $amount = Arr::get($response, 'orderInformation.amountDetails.totalAmount')
                            ?? Arr::get($response, 'amount')
                            ?? Arr::get($payload, 'orderInformation.amountDetails.totalAmount')
                            ?? Arr::get($payload, 'amount')
                            ?? Arr::get($direction, 'amount');

                        $currency = Arr::get($response, 'orderInformation.amountDetails.currency')
                            ?? Arr::get($response, 'currency')
                            ?? Arr::get($payload, 'orderInformation.amountDetails.currency')
                            ?? Arr::get($payload, 'currency')
                            ?? Arr::get($direction, 'currency');

                        return $amount
                            ? ($amount.' ('.($currency ?? '').')')
                            : 'N/A';
                    }),
                TextColumn::make('method')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('method_action_id')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('payment_intent_id')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('status_code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (CybersourceApiLog $record) => 'Cybersource API Log #'.$record->id)
                    ->modalContent(fn (CybersourceApiLog $record) => view('filament.modals.cybersource-api-log-view', ['log' => $record])),
            ]);
    }

    public function render(): View
    {
        return view('livewire.cybersource-api-log-table');
    }
}
