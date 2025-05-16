<?php

namespace App\Livewire\PricingRules;

use App\Helpers\ClassHelper;
use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use App\Models\Property;
use Carbon\Carbon;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\HotelContentRepository\Models\Product;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use HasPricingRuleFields;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $productId = null;

    public array $giataKeyIds = [];

    public ?string $rateCode = null;

    public bool $isSrCreator = false;

    public function mount(?int $productId = null, bool $isSrCreator = false, ?string $rateCode = null): void
    {
        $this->productId = $productId;
        $this->isSrCreator = $isSrCreator;
        $this->rateCode = $rateCode;
        if ($this->productId) {
            $this->giataKeyIds = Product::with(['related' => function ($query) {
                $query->select('id', 'giata_code');
            }])
                ->where('id', $this->productId)
                ->get()
                ->pluck('related.giata_code')
                ->flatten()
                ->toArray();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function () {
                $query = PricingRule::query();

                if (! empty($this->giataKeyIds)) {
                    $query->whereHas('conditions', function ($query) {
                        $query->where('field', 'property')
                            ->where(function ($query) {
                                $query->whereJsonContains('value', $this->giataKeyIds)
                                    ->orWhere('value_from', $this->giataKeyIds[0]);
                            });
                    });
                } elseif ($this->isSrCreator) {
                    return PricingRule::query()->whereRaw('1 = 0');
                }
                if ($this->rateCode) {
                    // When rateCode is set, filter only by 'rate_code' and 'property' conditions
                    $query->whereHas('conditions', function ($query) {
                        $query->where('field', 'rate_code')
                            ->where('value_from', $this->rateCode);
                    });

                    // Also, include the 'property' field but exclude other 'rate_code' conditions
                    $query->orWhereHas('conditions', function ($query) {
                        $query->where('field', 'property')
                            ->where(function ($query) {
                                $query->whereJsonContains('value', $this->giataKeyIds)
                                    ->orWhere('value_from', $this->giataKeyIds[0]);
                            });
                    });

                    // Ensure no other rate_code conditions are included
                    $query->whereDoesntHave('conditions', function ($query) {
                        $query->where('field', 'rate_code')->where('value_from', '!=', $this->rateCode);
                    });
                }

                return $query;
            })
            ->deferLoading()
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $record->conditions->contains('field', 'rate_code') => 'Rate',
                            $this->productId !== null => 'Hotel',
                            default => '',
                        };
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                        'success' => 'Room',
                    ]),
                TextColumn::make('name')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
//                TextColumn::make('weight')
//                    ->searchable()
//                    ->toggleable()
//                    ->extraAttributes(['style' => 'max-width: 100px;']),
                TextColumn::make('is_exclude_action')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'danger' => fn ($state) => $state === true,
                        'success' => fn ($state) => $state === false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Exclusion Rule' : 'Inclusion Rule')
                    ->toggleable(),
                TextColumn::make('conditions')
                    ->label('Property')
                    ->html()
                    ->wrap()
                    ->formatStateUsing(function ($state) {
                        $state = trim($state, " \t\n\r\0\x0B\"");
                        $state = '['.$state.']';
                        $data = json_decode($state, true);
                        $filteredData = array_filter($data, function ($item) {
                            return $item['field'] === 'property' && (! is_null($item['value']) || ! is_null($item['value_from']));
                        });
                        $result = array_map(function ($item) {
                            $propertyId = $item['value'] ?? $item['value_from'];
                            $properties = Property::whereIn('code', (array) $propertyId)->get();

                            return implode('<br>', $properties->map(function ($property) {
                                return '<b>'.$property->name.'</b> ('.$property->code.')';
                            })->toArray());
                        }, $filteredData);

                        return implode('<br>', array_values($result));
                    })
                    ->searchable(
                        query: function ($query, $search) {
                            $this->applyPropertyConditions($query, $search);
                        }
                    )
                    ->toggleable(),
                TextColumn::make('rule_start_date')
                    ->label('Rule Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('rule_expiration_date')
                    ->label('Rule End Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date()
                    ->formatStateUsing(function ($state) {
                        $date = Carbon::parse($state)->format('M j, Y');

                        return $date === 'Feb 2, 2112' ? '' : $date;
                    }),
                TextColumn::make('manipulable_price_type')
                    ->label('Price Type')
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record?->is_exclude_action
                        ? ''
                        : match ($state) {
                            'total_price' => 'Total Price',
                            'net_price' => 'Net Price',
                            'exclude_action' => '',
                            default => $state,
                        }
                    ),
                TextColumn::make('price_value_type')
                    ->label('Value Type')
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->formatStateUsing(fn ($state, $record) => $record?->is_exclude_action
                        ? ''
                        : match ($state) {
                            'fixed_value' => 'Fixed Value',
                            'percentage' => 'Percentage',
                            'exclude_action' => '',
                            default => $state,
                        }
                    ),
                TextColumn::make('price_value')
                    ->label('Value')
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record?->is_exclude_action
                        ? ''
                        : $state
                    )
                    ->icon(function ($record) {
                        return $record?->is_exclude_action
                            ? false
                            : match ($record->price_value_type) {
                                null, '' => false,
                                'fixed_value' => 'heroicon-o-banknotes',
                                'percentage' => 'heroicon-o-receipt-percent',
                                'exclude_action' => 'heroicon-o-banknotes',
                                default => '',
                            };
                    }),
                TextColumn::make('price_value_target')
                    ->label('Value Target')
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record?->is_exclude_action
                        ? ''
                        : match ($state) {
                            ProductApplyTypeEnum::PER_ROOM->value => 'Per Room',
                            ProductApplyTypeEnum::PER_PERSON->value => 'Per Person',
                            ProductApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                            'not_applicable' => 'N/A',
                            'exclude_action' => '',
                            default => $state,
                        }
                    ),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (PricingRule $record): string => route('pricing-rules.edit', array_merge(['pricing_rule' => $record], [
                            'sr' => $this->isSrCreator,
                            'gc' => $this->giataKeyIds[0] ?? null,
                            'rc' => $this->rateCode ?? null,
                        ])))
                        ->visible(fn (PricingRule $record): bool => Gate::allows('update', $record) && ! $this->isSrCreator),

                    EditAction::make()
                        ->modalHeading('Edit Pricing Rule')
                        ->modalWidth('7xl')
                        ->form(fn (PricingRule $record) => $this->pricingRuleFields('edit'))
                        ->fillForm(function (PricingRule $record) {
                            $data = $record->attributesToArray();
                            $data['rule_start_date'] = optional($record->rule_start_date)->format('Y-m-d');
                            $data['rule_expiration_date'] = optional($record->rule_expiration_date)->format('Y-m-d');
                            $data['conditions'] = $record->conditions->toArray();

                            return $data;
                        })
                        ->action(function (array $data, PricingRule $record) {
                            $conditions = $data['conditions'] ?? [];
                            unset($data['conditions']);
                            $record->update($data);
                            $record->conditions()->delete();
                            foreach ($conditions as $condition) {
                                $record->conditions()->create($condition);
                            }
                        })
                        ->visible(fn (PricingRule $record): bool => Gate::allows('update', $record) && $this->isSrCreator),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (PricingRule $record) => $record->delete())
                        ->visible(fn (PricingRule $record): bool => Gate::allows('delete', $record)),
                ])
                    ->visible(fn (PricingRule $record): bool => (
                        $this->productId && ! $this->rateCode) ||
                        ($this->productId && $this->rateCode && $record->conditions->contains('field', 'rate_code'))
                        || (! $this->productId && ! $this->rateCode)
                    ),
            ])
            ->headerActions([
                Action::make('Create in Modal')
                    ->tooltip('Create Pricing Rule in Modal')
                    ->modalHeading('Create Pricing Rule')
                    ->modalWidth('7xl')
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form($this->pricingRuleFields('create'))
                    ->fillForm(function () {
                        $data = [];
                        $data['is_sr_creator'] = $this->isSrCreator;
                        if ($this->giataKeyIds[0]) {
                            $data['conditions'] = [
                                [
                                    'field' => 'property',
                                    'compare' => '=',
                                    'value_from' => $this->giataKeyIds[0],
                                ],
                            ];
                            if ($this->rateCode) {
                                $data['conditions'][] = [
                                    'field' => 'rate_code',
                                    'compare' => '=',
                                    'value_from' => $this->rateCode,
                                ];
                            }
                        }
                        $data['rule_start_date'] = optional(now())->format('Y-m-d');

                        return $data;
                    })
                    ->action(function (array $data) {
                        $data['is_sr_creator'] = $this->isSrCreator;
                        /** @var CreatePricingRule $createPricingRule */
                        $createPricingRule = app(CreatePricingRule::class);
                        $createPricingRule->create($data);
                    })
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->visible(fn (): bool => Gate::allows('create', PricingRule::class) && $this->isSrCreator),

                CreateAction::make()
                    ->tooltip('Create Pricing Rule')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(route('pricing-rules.create', [
                        'sr' => $this->isSrCreator,
                        'gc' => $this->giataKeyIds[0] ?? null,
                        'rc' => $this->rateCode ?? null,
                    ]))
                    ->visible(fn (): bool => Gate::allows('create', PricingRule::class) && ! $this->isSrCreator),
            ])
            ->filters([
                Filter::make('destination')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('destination')
                                    ->label('Destination')
                                    ->native(false)
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        return \App\Models\PricingRuleCondition::query()
                                            ->where('field', 'destination')
                                            ->where('compare', '=')
                                            ->pluck('value_from')
                                            ->mapWithKeys(function ($cityId) {
                                                $giataGeography = \App\Models\GiataGeography::where('city_id', $cityId)->first();

                                                return [$cityId => $giataGeography ? "{$giataGeography->city_name} ({$giataGeography->city_id})" : $cityId];
                                            })
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('clear_hotel_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('destination',  []);
                                        }),
                                ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['destination'])) {
                            $propertyCodes = \App\Models\Property::whereIn('city_id', $data['destination'])
                                ->pluck('code')
                                ->toArray();

                            if (! empty($propertyCodes)) {
                                $query->whereHas('conditions', function (Builder $query) use ($propertyCodes, $data) {
                                    $query->where('field', 'property')
                                        ->where('compare', '=')
                                        ->whereIn('value_from', $propertyCodes)
                                        ->orWhere(function (Builder $query) use ($data) {
                                            $query->where('field', 'destination')
                                                ->where('compare', '=')
                                                ->whereIn('value', $data['destination']);
                                        });
                                });
                            }
                        }

                        return $query;
                    })
                    ->columnSpan(2),
                Filter::make('property')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('property')
                                    ->label('Property')
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        $propertyCodes = \App\Models\PricingRuleCondition::query()
                                            ->where('field', 'property')
                                            ->where('compare', '=')
                                            ->pluck('value_from')
                                            ->toArray();

                                        return \App\Models\Property::whereIn('code', $propertyCodes)
                                            ->get()
                                            ->mapWithKeys(function ($property) {
                                                return [$property->code => "{$property->name} ({$property->code})"];
                                            })
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('clear_hotel_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('property', []);
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['property'])) {
                            $query->whereHas('conditions', function (Builder $query) use ($data) {
                                $query->where('field', 'property')
                                    ->where('compare', '=')
                                    ->whereIn('value_from', $data['property']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),
                Filter::make('rate_code')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('rate_code')
                                    ->label('Rate Code')
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        return \App\Models\PricingRuleCondition::query()
                                            ->where('field', 'rate_code')
                                            ->where('compare', '=')
                                            ->pluck('value_from', 'value_from')
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('clear_hotel_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('rate_code', []);
                                        }),
                                ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['rate_code'])) {
                            $query->whereHas('conditions', function (Builder $query) use ($data) {
                                $query->where('field', 'rate_code')
                                    ->where('compare', '=')
                                    ->whereIn('value_from', $data['rate_code']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),
                Filter::make('room_name')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('room_name')
                                    ->label('Room Name')
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        return \App\Models\PricingRuleCondition::query()
                                            ->where('field', 'room_name')
                                            ->where('compare', '=')
                                            ->pluck('value_from', 'value_from')
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('clear_hotel_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('room_name', []);
                                        }),
                                ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['room_name'])) {
                            $query->whereHas('conditions', function (Builder $query) use ($data) {
                                $query->where('field', 'room_name')
                                    ->where('compare', '=')
                                    ->whereIn('value_from', $data['room_name']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),
                Filter::make('room_type')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('room_type')
                                    ->label('Room Type')
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        return \App\Models\PricingRuleCondition::query()
                                            ->where('field', 'room_type')
                                            ->where('compare', '=')
                                            ->pluck('value_from', 'value_from')
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('clear_hotel_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('room_type', []);
                                        }),
                                ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['room_type'])) {
                            $query->whereHas('conditions', function (Builder $query) use ($data) {
                                $query->where('field', 'room_type')
                                    ->where('compare', '=')
                                    ->whereIn('value_from', $data['room_type']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),
            ])
            ->filtersFormColumns(4);
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.pricing-rules-table');
    }

    protected function applyPropertyConditions($query, $value)
    {
        if (is_numeric($value)) {
            $query->whereHas('conditions', function ($query) use ($value) {
                $query->where('field', 'property')
                    ->where(function ($query) use ($value) {
                        $query->where('value', 'like', "%$value%")
                            ->orWhere('value_from', 'like', "%$value%");
                    });
            });
        } else {
            $propertyCodesFromConditions = PricingRuleCondition::where('field', 'property')
                ->get()
                ->flatMap(function ($condition) {
                    $values = is_array($condition->value) ? array_filter($condition->value) : array_filter([$condition->value]);
                    $valueFroms = is_array($condition->value_from) ? array_filter($condition->value_from) : array_filter([$condition->value_from]);

                    return array_merge($values, $valueFroms);
                })
                ->toArray();
            $propertyCodes = Property::whereIn('code', $propertyCodesFromConditions)
                ->where('name', 'like', "%$value%")
                ->pluck('code')
                ->toArray();
            foreach ($propertyCodes as $code) {
                $query->whereHas('conditions', function ($query) use ($code) {
                    $query->where('field', 'property')
                        ->where(function ($query) use ($code) {
                            $query->where('value', 'like', "%$code%")
                                ->orWhere('value_from', 'like', "%$code%");
                        });
                });
            }
        }
    }
}
