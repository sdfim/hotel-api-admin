<?php

namespace App\Livewire\Inspectors;

use App\Helpers\ClassHelper;
use App\Jobs\ProcessFlowScenario;
use App\Livewire\Components\CustomRepeater;
use App\Models\ApiSearchInspector;
use App\Models\Channel;
use App\Models\Enums\RoleSlug;
use App\Models\Mapping;
use App\Models\Property;
use App\Models\Supplier;
use App\Models\User;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\SupplierNameEnum;

class SearchInspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $isModalOpen = false;

    public $formData = [
        'type' => 'hotel',
        'checkin' => '2026-05-05',
        'checkout' => '2026-05-06',
        'giata_ids' => [38049404],
        'occupancy' => [],
    ];

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function submitForm()
    {
        // Handle form submission logic here
        $this->isModalOpen = false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make('add-flow-scepario')
                    ->label('Add Flow Scenario')
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->createAnother(false)
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(function ($data) {
                        $data['blueprint_exist'] = false;
                        ProcessFlowScenario::dispatch($data, auth()->user());

                        Notification::make()
                            ->title('Flow Scenario is being processed')
                            ->body('The task has been added to the queue.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Add Flow Scenario')
                    ->modalWidth('4xl')
                    ->form($this->getFormSchema())
                    //                    ->visible(fn () => config('superuser.email') === auth()->user()->email),
                    ->visible(fn () => auth()->user()?->roles()->where('slug', 'admin')->exists()),

            ])
            ->paginated([5, 10, 25, 50])
            ->query(ApiSearchInspector::query())
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ViewColumn::make('search_id')
                    ->tooltip('view Search ID data')
                    ->searchable(isIndividual: true)
                    ->view('dashboard.search-inspector.column.search-id'),
                TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->color(fn (string $state): string => match ($state) {
                        'error' => 'danger',
                        'success' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('search_type')
                    ->label('Type')
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'price' => 'success',
                        'check_quote' => 'warning',
                        'change' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('request')
                    ->label('Destination')
                    ->wrap()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        $giataIds = json_decode($record->request, true)['giata_ids'] ?? [];
                        if (! is_array($giataIds) || empty($giataIds)) {
                            return '';
                        }
                        $codes = array_slice($giataIds, 0, 3);
                        $properties = Property::whereIn('code', $codes)->pluck('name', 'code')->toArray();
                        $result = [];
                        foreach ($codes as $code) {
                            $name = $properties[$code] ?? '';
                            $result[] = $name ? ("$code | $name ") : $code;
                        }

                        return implode(', ', $result);
                    }),

                ViewColumn::make('view error data')
                    ->label('')
                    ->view('dashboard.search-inspector.column.error-data'),

                ViewColumn::make('request-btn')
                    ->label('')
                    ->view('dashboard.search-inspector.column.request'),

                ViewColumn::make('request-data')
                    ->label('Rooms')
                    ->toggleable()
                    ->view('dashboard.search-inspector.column.request-data'),

                TextColumn::make('token.name')
                    ->label('Channel')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('suppliers')
                    ->wrap()
                    ->width(200)
                    ->toggleable()
                    ->formatStateUsing(function (ApiSearchInspector $record): string {
                        return Supplier::whereIn('id', explode(',', $record->suppliers))->pluck('name')->implode(', ');
                    })
                    ->searchable(
                        query: function ($query, string $search) {
                            $matchingIds = Supplier::where('name', 'like', "%{$search}%")->pluck('id')->toArray();

                            if (empty($matchingIds)) {
                                $query->whereRaw('0 = 1');

                                return;
                            }

                            foreach ($matchingIds as $id) {
                                $query->orWhereRaw('FIND_IN_SET(?, suppliers)', [$id]);
                            }
                        },
                        isIndividual: true
                    )
                    ->extraAttributes(['data-custom-search' => 'suppliers']),
                TextColumn::make('created_at')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->formatStateUsing(function (ApiSearchInspector $record) {
                        return \App\Helpers\TimezoneConverter::convertUtcToEst($record->created_at);
                    }),
            ])
            ->actions([
                Action::make('edit-and-repeat')
                    ->label('')
                    ->tooltip('Edit and Repeat Flow Scenario')
                    ->icon('heroicon-o-fire')
                    ->fillForm(function ($record) {
                        $input = json_decode($record->request, true);
                        $token_id = Arr::get($input, 'token_id');
                        $channel = Channel::where('access_token', 'like', "%$token_id")->first();
                        $apiUser = $channel?->apiUsers?->first();
                        if (! $apiUser) {
                            $apiUser = User::whereHas('roles', function ($query) {
                                $query->where('slug', RoleSlug::API_USER->value);
                            })->first();
                        }
                        $input['api_user'] = $apiUser->email;
                        foreach ($input['occupancy'] as $key => $occupancy) {
                            if (isset($occupancy['room_type'])) {
                                $input['occupancy'][$key]['room_code'] = $occupancy['room_type'];
                                unset($input['occupancy'][$key]['room_type']);
                            }
                            if (isset($occupancy['rate_plan_code'])) {
                                $input['occupancy'][$key]['rate_code'] = $occupancy['rate_plan_code'];
                                unset($input['occupancy'][$key]['rate_plan_code']);
                            }
                        }
                        if (empty($input['supplier'])) {
                            $input['supplier'] = [SupplierNameEnum::ORACLE->value];
                        }
                        if (empty($input['currency'])) {
                            $input['currency'] = 'USD';
                        }

                        return $input;
                    })
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit and Repeat Flow Scenario')
                    ->modalWidth('4xl')
                    ->action(function ($data) {
                        $data['blueprint_exist'] = false;
                        ProcessFlowScenario::dispatch($data, auth()->user());

                        Notification::make()
                            ->title('Flow Scenario is being processed')
                            ->body('The task has been added to the queue.')
                            ->success()
                            ->send();
                    })
                    //                    ->visible(fn () => config('superuser.email') === auth()->user()->email),
                    ->visible(fn () => auth()->user()?->roles()->where('slug', 'admin')->exists()),

            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ApiSearchInspector::destroy($records->pluck('search_id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => config('superuser.email') === auth()->user()->email),
            ]);
    }

    private function getFormSchema(): array
    {
        return [
            Grid::make('')->schema([
                Select::make('api_user')
                    ->label('API User')
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('slug', RoleSlug::API_USER->value);
                    })->pluck('name', 'email')->toArray())
                    ->default(function () {
                        $user = User::whereHas('roles', function ($query) {
                            $query->where('slug', RoleSlug::API_USER->value);
                        })->first();

                        return $user ? $user->email : '';
                    }),
                Select::make('currency')
                    ->label('Currency')
                    ->options([
                        '*' => 'ALL',
                        'USD' => 'USD',
                        'MXN' => 'MXN',
                        'EUR' => 'EUR',
                    ])
                    ->default('*'),
            ])->columns(2),
            Grid::make('')->schema([
                TextInput::make('type')
                    ->label('Type')
                    ->required()
                    ->default('hotel'),
                Select::make('supplier')
                    ->label('Supplier')
                    ->options(Supplier::all()->pluck('name', 'name')->toArray())
                    ->default([SupplierNameEnum::ORACLE->value]),
            ])->columns(2),
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
            Select::make('giata_ids')
                ->label('Hotels (GIATA IDs)')
                ->required()
                ->multiple()
                ->options(Mapping::all()->mapWithKeys(function ($mapping) {
                    $property = $mapping->property;
                    $name = $property ? $property->name : '';

                    return [$mapping->giata_id => $mapping->giata_id.' | '.$name];
                })),
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
                                ->required()
                                ->default(2),
                            TagsInput::make('children_ages')
                                ->label('Children Ages')
                                ->required()
                                ->placeholder('Ages 1-13')
                                ->rules([
                                    'array',
                                    fn () => function (string $attribute, $value, $fail) {
                                        foreach ($value as $age) {
                                            if (! is_numeric($age) || $age < 1 || $age > 13 || floor($age) != $age) {
                                                $fail("Age {$age} must be an integer between 1 and 13.");
                                            }
                                        }
                                    },
                                ]),
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
                            Textarea::make('special_request')
                                ->label('')
                                ->placeholder('Special Request'),
                            Textarea::make('comment')
                                ->label('')
                                ->placeholder('Comment'),
                        ])->columns(2)
                            ->visible(fn (Get $get) => $get('../../run_booking_flow')),
                    ])->columns(1),
                ]),
            Grid::make('')->schema([
                Toggle::make('run_booking_flow')
                    ->label('Run Booking Flow')
                    ->default(true)
                    ->reactive(),
                Toggle::make('run_cancellation_flow')
                    ->label('Run Cancellation Flow')
                    ->default(true),
            ])->columns(2),
        ];
    }

    public function render(): View
    {
        return view('livewire.inspectors.search-inspector-table', [
            'isModalOpen' => $this->isModalOpen,
            'formData' => $this->formData,
        ]);
    }
}
