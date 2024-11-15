<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use App\Models\Property;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(PricingRule::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('conditions')
                    ->label('Property')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $state = trim($state, " \t\n\r\0\x0B\"");
                        $state = '[' . $state . ']';
                        $data = json_decode($state, true);
                        $filteredData = array_filter($data, function ($item) {
                            return $item['field'] === 'property' && (!is_null($item['value']) || !is_null($item['value_from']));
                        });
                        $result = array_map(function ($item) {
                            $propertyId = $item['value'] ?? $item['value_from'];
                            $properties = Property::whereIn('code', (array) $propertyId)->get();
                            return implode('<br>', $properties->map(function ($property) {
                                return '<b>' . $property->name . '</b> (' . $property->code . ')';
                            })->toArray());
                        }, $filteredData);

                        return implode('<br>', array_values($result));
                    })
                    ->searchable(
                        isIndividual: true,
                        query: function ($query, $search) {
                            $this->applyPropertyConditions($query, $search);
                        }
                    )
                    ->toggleable(),
                TextColumn::make('rule_start_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('rule_expiration_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date()
                    ->formatStateUsing(function ($state) {
                        $date = Carbon::parse($state);
                        return $date->format('Y-m-d') === '2112-02-02' ? '' : $date->format('M j, Y');
                    }),
                TextColumn::make('manipulable_price_type')
                    ->toggleable(),
                TextColumn::make('price_value_type')
                    ->toggleable(),
                TextColumn::make('price_value')
                    ->toggleable(),
                TextColumn::make('price_value_target')
                    ->toggleable(),
                //                TextColumn::make('rules')
                //                    ->toggleable(),
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
                    ViewAction::make()
                        ->url(fn (PricingRule $record): string => route('pricing-rules.show', $record)),
                    EditAction::make()
                        ->url(fn (PricingRule $record): string => route('pricing-rules.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (PricingRule $record) => $record->delete()),
                ]),
            ])
            ->filters([
                Filter::make('property_filter')
                    ->label('Property Filter')
                    ->query(function ($query, array $data) {
                        $value = $data['property'] ?? null;
                        if (!empty($value)) {
                            $this->applyPropertyConditions($query, $value);
                        }
                    })
                    ->form([
                        TextInput::make('property')
                            ->label('Property Code/Name')
                            ->placeholder('Enter property code or name'),
                    ]),
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
