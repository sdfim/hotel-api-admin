<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use App\Repositories\ApiBookingInspectorRepository;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Summarizers\Summarizer;
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
            ->query(ApiBookingPaymentInit::query())
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('booking_id')
                    ->label('Booking ID')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => (string) $state)
                    ->action(fn ($record) => $this->filterByBookingId($record->booking_id)),

                TextColumn::make('booking_cost')
                    ->label('Booking Cost')
                    ->numeric(2)
                    ->toggleable()
                    ->sortable()
                    ->summarize(new class extends Summarizer
                    {
                        public function summarize($query, string $attribute): mixed
                        {
                            $bookingIds = $query->distinct()->pluck('booking_id');
                            $sum = 0;
                            foreach ($bookingIds as $bookingId) {
                                $cost = ApiBookingInspectorRepository::getPriceBookingId($bookingId);
                                $sum += $cost ?? 0;
                            }

                            return number_format($sum, 2);
                        }
                    }),

                TextColumn::make('init_amount')
                    ->numeric(2)
                    ->label('Init')
                    ->formatStateUsing(fn ($state, $record) => $record->init_amount ? number_format($record->init_amount, 2) : '')
                    ->summarize(new class extends Summarizer
                    {
                        public function summarize(\Illuminate\Database\Query\Builder $query, string $attribute): mixed
                        {
                            $sum = $query->where('action', 'init')->sum('amount');

                            return number_format($sum, 2);
                        }
                    }),

                TextColumn::make('init_currency')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('confirmed_amount')
                    ->numeric(2)
                    ->label('Confirmed')
                    ->formatStateUsing(fn ($state, $record) => $record->confirmed_amount ? number_format($record->confirmed_amount, 2) : '')
                    ->summarize(new class extends Summarizer
                    {
                        public function summarize(\Illuminate\Database\Query\Builder $query, string $attribute): mixed
                        {
                            $sum = $query->where('action', 'confirmed')->sum('amount');

                            return number_format($sum, 2);
                        }
                    }),

                TextColumn::make('confirmed_currency')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('m/d/Y H:i:s')),
            ])
            ->filters([
                Filter::make('booking_id')
                    ->form([
                        \Filament\Forms\Components\Select::make('booking_id')
                            ->label('Booking ID')
                            ->options(ApiBookingPaymentInit::query()->pluck('booking_id', 'booking_id')->toArray()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['booking_id'])) {
                            $query->where('booking_id', $data['booking_id']);
                        }

                        return $query;
                    })
                    ->indicateUsing(fn (array $data) => ! empty($data['booking_id']) ? 'Booking ID: '.$data['booking_id'] : null),

                Filter::make('action')
                    ->form([
                        \Filament\Forms\Components\Select::make('action')
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
                    ->indicateUsing(fn (array $data) => ! empty($data['action']) ? 'Status: '.implode(', ', (array) $data['action']) : null),
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.api-booking-payment-init-table');
    }

    public function filterByBookingId($bookingId)
    {
        $this->tableFilters = [
            'booking_id' => [
                'booking_id' => $bookingId,
            ],
        ];
    }
}
