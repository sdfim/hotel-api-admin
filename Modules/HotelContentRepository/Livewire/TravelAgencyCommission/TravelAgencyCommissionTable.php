<?php

namespace Modules\HotelContentRepository\Livewire\TravelAgencyCommission;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\CommissionValueTypeEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use Modules\HotelContentRepository\Models\TravelAgencyCommissionCondition;

class TravelAgencyCommissionTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasProductActions;

    public int $productId;
    public string $title;

    public function mount(int $productId)
    {
        $this->productId = $productId;
        $product = Product::find($productId);
        $this->title = 'Travel Agency Commission for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            TextInput::make('name')
                ->label('Commission Name')
                ->required(),
            TextInput::make('commission_value')
                ->label('Commission Value')
                ->numeric('decimal')
                ->required(),
            Select::make('commission_value_type')
                ->label('Commission Value Type')
                ->options(array_column(CommissionValueTypeEnum::cases(), 'value', 'value'))
                ->required(),
            Grid::make()->schema([
                DatePicker::make('date_range_start')
                    ->label('Start Date')
                    ->native(false)
                    ->default(fn() => now())
                    ->required(),
                DatePicker::make('date_range_end')
                    ->label('End Date')
                    ->native(false)
                    ->required(),
            ]),

            Repeater::make('conditions')
                ->label('Related Model')
                ->schema([
                    Grid::make()->schema([
                        Select::make('field')
                            ->label('Field')
                            ->options([
                                'consortia' => 'Consortia',
                                'room_type' => 'Room Type',
                            ])
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn(Select $component) => $component
                                ->getContainer()
                                ->getComponent('dynamicFieldValue')
                                ->getChildComponentContainer()
                                ->fill()
                            ),
                        Grid::make()
                            ->schema(components: fn(Get $get): array => match ($get('field')) {
                                'consortia' => [
                                    Select::make('value')
                                        ->label('Consortia')
                                        ->options(ConfigConsortium::all()->pluck('name', 'id'))
                                        ->required(),
                                ],
                                'room_type' => [
                                    TextInput::make('value')
                                        ->label('Room Type')
                                        ->required(),
                                ],
                                default => []
                            })
                            ->columns(1)
                            ->columnStart(2)
                            ->key('dynamicFieldValue')
                    ]),


                ])
                ->createItemButtonLabel('Add Conditions')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(
                TravelAgencyCommission::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Commission Name')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('conditions')
                    ->label('Describe')
                    ->formatStateUsing(function ($state) {
                        $items = explode(', ', $state);
                        $string = '';
                        foreach ($items as $item) {
                            $dataItem = json_decode($item, true);
                            if ($dataItem['field'] === 'consortia') {
                                $string .= $dataItem['field'] . ': ' . ConfigConsortium::where('id', $dataItem['value'])->first()->name . '</b><br>';
                            } else {
                                $string .= $dataItem['field'] . ': <b>' . $dataItem['value'] . '</b><br>';
                            }
                        }
                        return $string;
                    })
                    ->html()
                    ->searchable(),
                TextColumn::make('commission_value')
                    ->label('Value')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('commission_value_type')
                    ->label('Value Type')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date_range_start')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('date_range_end')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Travel Agency Commission')
                    ->form($this->schemeForm())
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->fillForm(function (TravelAgencyCommission $record) {
                        $data = $record->toArray();
                        $data['conditions'] = $record->conditions->toArray();
                        return $data;
                    })
                    ->visible(fn (TravelAgencyCommission $record): bool => Gate::allows('update', $record))
                    ->action(function (TravelAgencyCommission $record, array $data) {
                        $conditions = $data['conditions'] ?? [];
                        unset($data['conditions']);
                        $record->update($data);
                        $record->conditions()->delete();
                        foreach ($conditions as $condition) {
                            $record->conditions()->create($condition);
                        }
                        return $data;
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn (TravelAgencyCommission $record): bool => Gate::allows('delete', $record))
                ,
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->productId ? [
                            'product_id' => $this->productId,
                            'date_range_start' => now(),
                        ] : [];
                    })
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        $conditions = $data['conditions'] ?? [];
                        unset($data['conditions']);
                        $travelAgencyCommission = TravelAgencyCommission::create($data);
                        foreach ($conditions as $condition) {
                            $travelAgencyCommission->conditions()->create($condition);
                        }
                        return $data;
                    })
                    ->createAnother(false)
                    ->tooltip('Add New Travel Agency Commission')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn (): bool => Gate::allows('create', TravelAgencyCommission::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.commissions.travel-agency-commission-table');
    }
}
