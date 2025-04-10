<?php

namespace App\Livewire\PricingRules;

use App\Helpers\ClassHelper;
use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use App\Models\Property;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
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
                    ->toggleable(),
                TextInputColumn::make('weight')
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(['style' => 'max-width: 100px;']),
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
                    ->toggleable(),
                TextColumn::make('price_value_type')
                    ->label('Value Type')
                    ->toggleable(),
                TextColumn::make('price_value')
                    ->label('Value')
                    ->toggleable(),
                TextColumn::make('price_value_target')
                    ->label('Value Target')
                    ->toggleable(),
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
            ]);
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
