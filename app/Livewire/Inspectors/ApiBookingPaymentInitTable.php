<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class ApiBookingPaymentInitTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ApiBookingPaymentInit::query()->latest())
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('booking_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('booking_cost')
                    ->label('Booking Cost')
                    ->numeric(2)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('payment_intent_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->url(function ($record) {
                        $providerRoutes = [
                            'airwallex' => 'airwallex-api-logs.index',
                            // Добавьте другие провайдеры и их роуты ниже
                            // 'provider_name' => 'route_name',
                        ];
                        $provider = $record->provider;
                        $routeName = $providerRoutes[$provider] ?? null;
                        return $routeName
                            ? route($routeName, ['id' => $record->related_id])
                            : null;
                    }, true),
                TextColumn::make('action')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof PaymentStatusEnum ? $state->value : $state) {
                        PaymentStatusEnum::INIT->value => 'warning',
                        PaymentStatusEnum::CONFIRMED->value => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->summarize(Sum::make()->formatStateUsing(fn ($state) => number_format($state, 2))),
                TextColumn::make('currency')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('provider')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('m/d/Y H:i:s')),
            ])
            ->filters([
                Filter::make('action')
                    ->form([
                        Select::make('action')
                            ->label('Payment Status')
                            ->multiple()
                            ->options(collect(PaymentStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name])->toArray()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['action'])) {
                            $query->whereIn('action', (array) $data['action']);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        if (! empty($data['action'])) {
                            return 'Status: '.implode(', ', (array) $data['action']);
                        }

                        return null;
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.api-booking-payment-init-table');
    }
}
