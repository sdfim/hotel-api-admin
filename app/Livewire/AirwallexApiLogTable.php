<?php

namespace App\Livewire;

use App\Models\AirwallexApiLog;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Url;

class AirwallexApiLogTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?AirwallexApiLog $selectedLog = null;

    public bool $showModal = false;

    #[Url]
    public ?string $id = null;

    public function viewLog(AirwallexApiLog $log): void
    {
        $this->selectedLog = $log;
        $this->showModal = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(AirwallexApiLog::query()->latest())
            ->modifyQueryUsing(function ($query) {
                if ($this->id) {
                    $query->where('id', $this->id);
                }
            })
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(),
                TextColumn::make('booking_id')->sortable()->searchable(isIndividual: true)->toggleable(),
                TextColumn::make('response')
                    ->label('Amount')
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(function ($record) {
                        $response = is_array($record->response)
                            ? $record->response
                            : json_decode($record->response, true);

                        return Arr::get($response, 'amount')
                            ? (Arr::get($response, 'amount').' ('.Arr::get($response, 'currency', '').')')
                            : 'N/A';
                    }),
                TextColumn::make('method')->sortable()->toggleable()->searchable(),
                TextColumn::make('payment_intent_id')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('status_code')->sortable()->toggleable()->searchable(),
                TextColumn::make('created_at')->sortable()->toggleable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (AirwallexApiLog $record) => 'Airwallex API Log #'.$record->id)
                    ->modalContent(fn (AirwallexApiLog $record) => view('filament.modals.airwallex-api-log-view', ['log' => $record])),
            ]);
    }

    public function render(): View
    {
        return view('livewire.airwallex-api-log-table');
    }
}
