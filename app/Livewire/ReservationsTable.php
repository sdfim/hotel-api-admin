<?php

namespace App\Livewire;

use App\Livewire\Components\CustomRepeater;
use App\Models\Enums\RoleSlug;
use App\Models\Mapping;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Payment\Controllers\PaymentController;
use Modules\API\Requests\BookingCancelBooking;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Reservation::query()->with('apiBookingsMetadata'))
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ViewColumn::make('reservation_contains')
                    ->searchable(isIndividual: true)
                    ->view('dashboard.reservations.column.contains'),
                TextColumn::make('channel.name')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('apiBookingsMetadata.supplier_booking_item_id')
                    ->fontFamily(FontFamily::Mono)
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->label('Confirmation')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode('<br>', array_map('trim', $state));
                        }
                        if (is_string($state)) {
                            return implode('<br>', array_map('trim', explode(',', $state)));
                        }

                        return '';
                    })
                    ->html(),
                TextColumn::make('apiBookingsMetadata.hotel_supplier_id')
                    ->label('Hotel Id')
                    ->toggleable()
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true),
                TextColumn::make('apiBookingsMetadata')
                    ->label('Hotel/Vendor')
                    ->wrap()
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->formatStateUsing(function ($state, $record) {
                        $hotelName = $record->apiBookingsMetadata?->hotel?->name ?? '';
                        $vendorName = $record->apiBookingsMetadata?->supplier?->name ?? '';

                        return "$hotelName ($vendorName)";
                    }),

                ImageColumn::make('images')
                    ->toggleable()
                    ->state(function (Reservation $record) {
                        $reservationContains = json_decode($record->reservation_contains, true);
                        $images = [];
                        if (isset($reservationContains['hotel_images'])) {
                            $images = json_decode($reservationContains['hotel_images']);
                        }
                        if (isset($reservationContains['flight_images'])) {
                            $images = json_decode($reservationContains['flight_images']);
                        }

                        return $images;
                    })
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->size(45)
                    ->limitedRemainingText(isSeparate: true)
                    ->url(fn (Reservation $record): string => route('reservations.show', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')
                    ->label('Offload')
                    ->default('N\A')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'N\A' => 'info',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('date_travel')
                    ->date()
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('passenger_surname')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true),

                TextColumn::make('total_cost')
                    ->numeric()
                    ->weight(FontWeight::Bold)
                    ->size(TextColumn\TextColumnSize::Large)
                    ->money(function (Reservation $reservation) {
                        $price = json_decode($reservation->reservation_contains, true)['price'] ?? [];

                        return $price['currency'] ?? 'USD';
                    })
                    ->color(function (Reservation $reservation) {
                        $currency = json_decode($reservation->reservation_contains, true)['price']['currency'] ?? 'USD';

                        return match ($currency) {
                            'EUR' => 'info',
                            'GBP' => 'danger',
                            'CAD' => 'warning',
                            'MXN' => 'gray',
                            default => 'success',
                        };
                    })
                    ->sortable(),

                TextColumn::make('paid')
                    ->label('Paid')
                    ->weight(FontWeight::Bold)
                    ->color('danger')
                    ->size(TextColumn\TextColumnSize::Large)
                    ->numeric()
                    ->money(function (Reservation $reservation) {
                        $price = json_decode($reservation->reservation_contains, true)['price'] ?? [];

                        return $price['currency'] ?? 'USD';
                    })
                    ->url(fn (Reservation $record): string => route('payment-inspector.index', ['booking_id' => $record->booking_id]))
                    ->openUrlInNewTab(),

                TextColumn::make('canceled_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn (Reservation $record) => $record->canceled_at ? 'Canceled' : 'Active')
                    ->badge()
                    ->color(fn ($state) => $state === 'Canceled' ? 'danger' : 'success'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (Reservation $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
                            $booking_id = $record->booking_id;
                            $booking_item = $record->booking_item;

                            // Create BookingCancelBooking request
                            $request = new BookingCancelBooking([
                                'booking_id' => $booking_id,
                                'booking_item' => $booking_item,
                            ]);

                            $handler = app(BookApiHandler::class);

                            // Call cancelBooking
                            $response = $handler->cancelBooking($request);
                            $result = $response->getData(true);

                            // Only update canceled_at if cancellation is successful
                            if (isset($result['success']) || (isset($result['result']) && empty($result['error']))) {
                                $record->update(['canceled_at' => date('Y-m-d H:i:s')]);
                            }
                        })
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->visible(fn (Reservation $record): bool => Gate::allows('update', $record) && $record->canceled_at === null),
                    Action::make('Close Remaining Balance')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
                            $remainingBalance = ApiBookingItemRepository::getDepositData($record->booking_id);

                            foreach ($remainingBalance as $balance) {
                                $controller = app(PaymentController::class);
                                $controller->createPaymentIntentMoFoF($record->booking_id, Arr::get($balance, 'total_deposit'));
                            }
                        })
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (Reservation $record): bool => Gate::allows('update', $record) && ($record->total_cost > $record->paid) && $record->canceled_at === null),
                    Action::make('change_booking')
                        ->label('Change Booking')
                        ->tooltip('Change Booking')
                        ->icon('heroicon-o-pencil-square')
                        ->fillForm(function ($record) {
                            $field = json_decode($record->reservation_contains, true);
                            $passengers_by_room = ApiBookingInspectorRepository::getPassengersByRoom($record->booking_id, $record->booking_item);
                            [$special_requests_by_room, $comments_by_room] = ApiBookingInspectorRepository::getSpecialRequestsAndComments($record->booking_id, $record->booking_item) ?? [];
                            $roomCodes = array_filter(explode(';', $field['price']['room_type'] ?? ''));
                            $rateCodes = explode(';', $field['price']['rate_plan_code'] ?? '');
                            $mealPlans = explode(';', $field['price']['meal_plan'] ?? '');

//                        dd($passengers_by_room);

                            $request = json_decode($record->apiBookingItem->search->request, true);

                            $input = [
                                'checkin' => $request['checkin'] ?? null,
                                'checkout' => $request['checkout'] ?? null,
                            ];
                            $i = 0;
                            foreach ($passengers_by_room as $occupancy) {
                                $input['occupancy'][$i]['room_code'] = $roomCodes[$i];
                                $input['occupancy'][$i]['rate_code'] = $rateCodes[$i] ?? null;
                                $input['occupancy'][$i]['meal_plan_code'] = $mealPlans[$i] ?? null;
                                $input['occupancy'][$i]['adults'] = $request['occupancy'][$i]['adults'] ?? 1;
                                $input['occupancy'][$i]['children_ages'] = $request['occupancy'][$i]['children_ages'] ?? [];
                                $input['occupancy'][$i]['special_request'] = $special_requests_by_room[$i + 1] ?? '';
                                $input['occupancy'][$i]['comment'] = $comments_by_room[$i + 1] ?? '';

                                $input['occupancy'][$i]['title'] = $occupancy[0]['title'] ?? '';
                                $input['occupancy'][$i]['given_name'] = $occupancy[0]['given_name'] ?? '';
                                $input['occupancy'][$i]['family_name'] = $occupancy[0]['family_name'] ?? '';
                                $input['occupancy'][$i]['date_of_birth'] = $occupancy[0]['date_of_birth'] ?? null;

                                $i++;
                            }

                            return $input;
                        })
                        ->form($this->getFormSchema())
                        ->modalHeading('Change Booking')
                        ->modalWidth('4xl')
                        ->action(function ($data) {


                            Notification::make()
                                ->title('Flow Scenario is being processed')
                                ->body('The task has been added to the queue.')
                                ->success()
                                ->send();
                        })
//                    ->visible(fn () => config('superuser.email') === auth()->user()->email),
                        ->visible(fn () => auth()->user()?->roles()->where('slug', 'admin')->exists()),
                ])->color('gray'),
            ])
            ->filters([
                SelectFilter::make('cancellation')
                    ->label('Booking Status')
                    ->options([
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        $query
                            ->when($value === 'active', fn (Builder $q) => $q->whereNull('canceled_at'))
                            ->when($value === 'canceled', fn (Builder $q) => $q->whereNotNull('canceled_at'));
                    }),
            ]);
    }

    private function getFormSchema(): array
    {
        return [
            Grid::make('')->schema([
                DatePicker::make('checkin')
                    ->label('Check-in Date')
                    ->native(false)
                    ->required()
                    ->default(now()->addMonths(5)->format('Y-m-d')),
                DatePicker::make('checkout')
                    ->label('Check-out Date')
                    ->native(false)
                    ->required()
                    ->default(now()->addMonths(5)->addDays(2)->format('Y-m-d')),
            ])->columns(2),
            CustomRepeater::make('occupancy')
                ->label('Room')
                ->schema([
                    Section::make('')->schema([
                        Grid::make('')->schema([
                            TextInput::make('adults')
                                ->label('Count of Adults')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(6)
                                ->required(),
                            TagsInput::make('children_ages')
                                ->label('Children Ages'),
                        ])->columns(2),
                        Grid::make('')->schema([
                            TextInput::make('room_code')
                                ->label('')
                                ->placeholder('Room Type'),
                            TextInput::make('rate_code')
                                ->label('')
                                ->placeholder('Rate Plan Code'),
                            TextInput::make('meal_plan_code')
                                ->label('')
                                ->placeholder('Meal Plan Code'),
                        ])->columns(3),
                        Grid::make('')->schema([
                            TextInput::make('title')
                                ->label('')
                                ->placeholder('Title'),
                            TextInput::make('given_name')
                                ->label('')
                                ->placeholder('Given Name'),
                            TextInput::make('family_name')
                                ->label('')
                                ->placeholder('Family Name'),
                            DatePicker::make('date_of_birth')
                                ->label('')
                                ->placeholder('Date of Birth')
                                ->native(false)
                                ->required()
                                ->default(now()->addMonths(5)->format('Y-m-d')),
                        ])->columns(4),
                        Grid::make('')->schema([
                            Textarea::make('special_request')
                                ->label('')
                                ->placeholder('Special Request'),
                            Textarea::make('comment')
                                ->label('')
                                ->placeholder('Comment'),
                        ])->columns(2),
                    ])->columns(1),
                ]),
        ];
    }


    public function render(): View
    {
        return view('livewire.reservations-table');
    }
}
