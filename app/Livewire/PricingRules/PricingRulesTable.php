<?php

namespace App\Livewire\PricingRules;

use App\Helpers\ClassHelper;
use App\Models\PricingRule;
use App\Models\Property;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;

class PricingRulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;
    public array $giataKeyIds = [];
    public bool $isSrCreator = false;

    public function mount(?int $hotelId = null, bool $isSrCreator = false): void
    {
        $this->hotelId = $hotelId;
        $this->isSrCreator = $isSrCreator;
        if ($this->hotelId) {
            $this->giataKeyIds = Hotel::with(['keyMappings' => function ($query) {
                $query->whereHas('keyMappingOwner', function ($query) {
                    $query->where('name', 'GIATA');
                });
            }])->where('id', $this->hotelId)->get()->pluck('keyMappings.*.key_id')->flatten()->toArray();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function () {
                $query = PricingRule::query();
                if (!empty($this->giataKeyIds)) {
                    $query->whereHas('conditions', function ($query) {
                        $query->where('field', 'property')
                            ->whereJsonContains('value', $this->giataKeyIds);
                    });
                }
                return $query;
            })
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
                            return $query->whereHas('conditions', function ($query) use ($search) {
                                $query->where('field', 'property')
                                    ->where('value', 'like', "%$search%");
                            });
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
                        $date = Carbon::parse($state)->format('M j, Y');
                        return $date === 'Feb 2, 2112' ? '' : $date;
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
//                    ViewAction::make()
//                        ->url(fn (PricingRule $record): string => route('pricing-rules.show', $record)),
                    EditAction::make()
                        ->url(fn (PricingRule $record): string => route('pricing-rules.edit', $record))
                        ->visible(fn (PricingRule $record): bool => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (PricingRule $record) => $record->delete())
                        ->visible(fn (PricingRule $record): bool => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(route('pricing-rules.create', [
                        'sr' => $this->isSrCreator,
                        'gc' => $this->giataKeyIds[0] ?? null,
                    ]))
                    ->visible(fn (): bool => Gate::allows('create', PricingRule::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.pricing-rules-table');
    }
}
