<?php

namespace Modules\HotelContentRepository\Livewire\ProductFeeTaxes;

use App\Helpers\ClassHelper;
use App\Models\Supplier;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Form;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Modules\API\Suppliers\Enums\HBSI\HbsiFeeTaxTypeEnum;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public ?int $roomId = null;

    public ?array $rateRoomIds = [];

    public string $title;

    public ?string $supplierType = null;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->roomId = $roomId;
        $rate = HotelRate::where('id', $rateId)->first();
        $this->rateRoomIds = $rate ? $rate->rooms->pluck('id')->toArray() : [];
        $room = HotelRoom::where('id', $roomId)->first();
        $this->title = 'Fees and Taxes for '.$product->name;
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
            $this->title .= ' - Rate Name: '.$rate->name;
        }
        if ($this->roomId) {
            $this->title .= ' - Room ID: '.$this->roomId;
            $this->title .= ' - Room Name: '.$room->name;
        }
    }

    public function updatedSupplierId($value)
    {
        $supplier = Supplier::find($value);
        $this->supplierType = $supplier ? $supplier->name : null;
    }

    protected function getSupplierFeeTaxOptions(): array
    {
        return match ($this->supplierType) {
            SupplierNameEnum::HBSI->value => collect(HbsiFeeTaxTypeEnum::cases())
                ->mapWithKeys(fn ($enum) => [$enum->value => $enum->value])
                ->toArray(),
            default => [],
        };
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('room_id')->default($this->roomId),
            Hidden::make('rate_id')->default($this->rateId),

            Placeholder::make('action_description')
                ->label('')
                ->content(function (Get $get) {
                    return match ($get('action_type')) {
                        'add' => 'You are adding a new fee or tax for the selected product and room.',
                        'update' => 'You are updating only the name of an existing fee or tax.',
                        'edit' => 'You are updating the name and modifying the values (e.g., amounts or percentages) of an existing fee or tax.',
                        'delete' => 'You are removing an existing fee or tax by specifying its current name.',
                        'vat' => 'You are configuring VAT: it subtracts the specified percentage from total_net and outputs it as a separate tax field in the breakdown.',
                        default => 'Please select an action type to view its description.',
                    };
                })
                ->inlineLabel()
                ->visible(fn (Get $get) => filled($get('action_type'))),

            Fieldset::make('General Setting')
                ->columns(2)
                ->schema([
                    Select::make('supplier_id')
                        ->label('Supplier/Driver')
                        ->rules(['required'])
                        ->options(
                            Supplier::query()
                                ->whereJsonContains('product_type', 'hotel')
                                ->where('name', SupplierNameEnum::HBSI->value)
                                ->pluck('name', 'id')
                        )
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            $this->updatedSupplierId($state);
                            $options = $this->getSupplierFeeTaxOptions();
                            $set('old_name', null);
                            $set('old_name_options', $options);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get, ?string $state) {
                            if ($state) {
                                $this->updatedSupplierId($state);
                                $options = $this->getSupplierFeeTaxOptions();
                                $set('old_name_options', $options);
                            }
                        }),
                    Select::make('action_type')
                        ->label('Action Type')
                        ->options([
                            'add' => 'Add',
                            'update' => 'Update',
                            'edit' => 'Edit',
                            'delete' => 'Delete',
                            'vat' => 'VAT management',
                        ])
                        ->live()
                        ->rules(['required']),
                    TextInput::make('name')
                        ->label('New Name')
                        ->reactive()
                        ->visible(fn (Get $get) => $get('action_type') !== 'delete' && $get('action_type') !== 'vat'),
                    TextInput::make('old_name')
                        ->label('Current Name')
                        ->datalist(fn (Get $get) => array_values($get('old_name_options') ?? []))
                        ->autocomplete('list')
                        ->visible(fn (Get $get) => $get('action_type') !== 'add' && $get('action_type') !== 'vat')
                        ->reactive()
                        ->rules(['required']),
                ]),

            Fieldset::make('Date Setting')
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Travel Start Date')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y')
                        ->rules(['date']),
                    DatePicker::make('end_date')
                        ->label('Travel End Date')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y')
                        ->rules(['date', 'after_or_equal:start_date']),
                ])
                ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

            Fieldset::make('Type Setting')
                ->columns(2)
                ->visible(fn (Get $get) => $get('action_type') !== 'delete')
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            ProductFeeTaxTypeEnum::TAX->value => 'Tax',
                            ProductFeeTaxTypeEnum::FEE->value => 'Fee',
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('commissionable')
                                ->label('Commissionable to TA')
                                ->inline(false),
                            Select::make('fee_category')
                                ->label('Fee Category')
                                ->options([
                                    'mandatory' => 'Mandatory',
                                    'optional' => 'Optional',
                                ]),
                        ])->columnSpan(1),
                ])
                ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

            Fieldset::make('Restrictions Setting')
                ->schema([
                    TextInput::make('age_from')
                        ->label('Age From')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(99),
                    TextInput::make('age_to')
                        ->label('Age To')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(99)
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $ageFrom = $get('age_from');
                                if (! is_null($ageFrom) && $value < $ageFrom) {
                                    $fail('The Age To must be greater than or equal to Age From.');
                                }
                            };
                        }),
                ])
                ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

            Fieldset::make('Value Setting')
                ->columns(2)
                ->visible(fn (Get $get) => $get('action_type') === 'add' || $get('action_type') === 'edit' || $get('action_type') === 'vat')
                ->schema([
                    Select::make('value_type')
                        ->label('Value Type')
                        ->options([
                            ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                            ProductFeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
                        ])
                        ->rules(['required'])
                        ->default(fn (Get $get) => ProductFeeTaxValueTypeEnum::PERCENTAGE->value)
                        ->disabled(fn (Get $get) => $get('action_type') === 'vat'),
                    Select::make('apply_type')
                        ->label('Apply Type')
                        ->options([
                            ProductApplyTypeEnum::PER_ROOM->value => 'Per Room',
                            ProductApplyTypeEnum::PER_PERSON->value => 'Per Person',
                            ProductApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                        ])
                        ->reactive()
                        ->rules(['required'])
                        ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

                    TextInput::make('net_value')
                        ->label('Net Value')
                        ->numeric(2)
                        ->rules(['required']),
                    TextInput::make('rack_value')
                        ->label('Rack Value')
                        ->numeric(2)
                        ->rules(['required'])
                        ->visible(fn (Get $get) => $get('action_type') !== 'vat'),
                ]),
            Grid::make(2)
                ->schema([
                    Select::make('collected_by')
                        ->label('Collected By')
                        ->options([
                            FeeTaxCollectedByEnum::DIRECT->value => 'Direct',
                            FeeTaxCollectedByEnum::VENDOR->value => 'Vendor',
                        ]),
                ])
                ->visible(fn (Get $get) => $get('action_type') !== 'vat'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductFeeTax::query()
                    ->where('product_id', $this->productId)
            )
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->rateId) {
                    $query->where(function ($q) {
                        $q->where('rate_id', $this->rateId)
                            ->orWhereNull('rate_id');
                    });
                    $query->where(function ($q) {
                        $q->whereIn('room_id', $this->rateRoomIds)
                            ->orWhereNull('room_id');
                    });
                } elseif ($this->roomId) {
                    $query->where(function ($q) {
                        $q->where('room_id', $this->roomId)
                            ->orWhereNull('rate_id')->whereNull('room_id');
                    });
                } else {
                    $query->whereNull('rate_id')->whereNull('room_id');
                }
            })
            ->deferLoading()
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $this->productId && $record->rate_id !== null => 'Rate',
                            $this->productId && $record->room_id !== null => 'Room',
                            default => 'Hotel',
                        };
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                        'success' => 'Room',
                    ]),
                TextColumn::make('code_entity')
                    ->toggleable()
                    ->label('Code')
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $record->rate_id !== null => $record->rate?->code,
                            in_array($record->room_id, $this->rateRoomIds) => $record->room->external_code,
                            default => '',
                        };
                    }),
                TextColumn::make('supplier.name')->label('Driver')->searchable(),
                TextColumn::make('action_type')
                    ->label('Action Type')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'primary' => 'add',
                        'warning' => 'edit',
                        'success' => 'update',
                        'danger' => 'delete',
                        'secondary' => 'vat',
                    ]),
                TextColumn::make('old_name')->label('Current Name')->searchable(),
                TextColumn::make('name')->label('New Name')->searchable(),
                TextColumn::make('type')->label('Type')->sortable(),
                TextColumn::make('net_value')->label('Net')->sortable(),
                TextColumn::make('rack_value')->label('Rack')->sortable(),
                IconColumn::make('value_type')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'heroicon-o-percent-badge',
                        ProductFeeTaxValueTypeEnum::AMOUNT->value => 'heroicon-o-currency-dollar',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('apply_type')->label('Apply Type')->sortable(),
                IconColumn::make('commissionable')
                    ->label(new HtmlString('<span title="Commissionable to Travel Agent">Comm.<br>to TA</span>'))
                    ->boolean(),

                TextColumn::make('fee_category')->label('Fee Category'),
                TextColumn::make('collected_by')->label('Collected By'),

                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form($this->schemeForm())
                        ->action(function (ProductFeeTax $record, array $data) {
                            if ($data['action_type'] === 'vat') {
                                $exists = ProductFeeTax::query()
                                    ->where('action_type', 'vat')
                                    ->where('product_id', $this->productId)
                                    ->exists();

                                if ($exists) {
                                    Notification::make()
                                        ->title('Validation Error')
                                        ->body('A VAT record already exists for this hotel.')
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }

                            if ($data['action_type'] === 'delete') {
                                // General Setting
                                $data['name'] = null;
                                // Type Setting
                                $data['type'] = null;
                                $data['fee_category'] = null;
                                // Value Setting
                                $data['value_type'] = null;
                                $data['apply_type'] = null;
                                $data['net_value'] = null;
                                $data['rack_value'] = null;
                            }
                            if ($data['action_type'] === 'add') {
                                // General Setting
                                $data['old_name'] = null;
                            }
                            if ($data['action_type'] === 'update') {
                                // Value Setting
                                $data['value_type'] = null;
                                $data['apply_type'] = null;
                                $data['net_value'] = null;
                                $data['rack_value'] = null;
                            }
                            $record->update($data);
                        })
                        ->closeModalByClickingAway(false)
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    Action::make('clone')
                        ->label('Clone')
                        ->icon('heroicon-o-clipboard-document')
                        ->color('warning')
                        ->modalHeading(new HtmlString("Clone {$this->title}"))
                        ->form($this->schemeForm())
                        ->mountUsing(function (Form $form, ProductFeeTax $record) {
                            $data = $record->toArray();
                            $data['name'] = $data['name'].' (Clone '.now()->format('Y-m-d H:i:s').')';
                            $form->fill($data);
                        })
                        ->action(function (array $data) {
                            if ($data['action_type'] === 'vat') {
                                $exists = ProductFeeTax::query()
                                    ->where('action_type', 'vat')
                                    ->where('product_id', $this->productId)
                                    ->exists();

                                if ($exists) {
                                    Notification::make()
                                        ->title('Validation Error')
                                        ->body('A VAT record already exists for this hotel.')
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }
                            if ($data['action_type'] === 'add') {
                                $data['old_name'] = null;
                            }
                            ProductFeeTax::create($data);
                        })
                        ->closeModalByClickingAway(false)
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])
                    ->visible(fn (ProductFeeTax $record): bool => ($this->rateId && $this->rateId === $record->rate_id)
                        || ($this->roomId && $this->roomId === $record->room_id)
                        || (! $this->rateId && ! $this->roomId)
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->modalWidth('7xl')
                    ->tooltip('Add New Entity')
                    ->icon('heroicon-o-plus')
                    ->visible(fn () => Gate::allows('create', Product::class))
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function (array $data) {
                        if ($data['action_type'] === 'vat') {
                            $data['type'] = ProductFeeTaxTypeEnum::TAX->value;
                            $data['value_type'] = ProductFeeTaxValueTypeEnum::PERCENTAGE->value;
                            $exists = ProductFeeTax::query()
                                ->where('action_type', 'vat')
                                ->where('product_id', $this->productId)
                                ->exists();

                            if ($exists) {
                                Notification::make()
                                    ->title('Validation Error')
                                    ->body('A VAT record already exists for this hotel.')
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }
                        ProductFeeTax::create($data);
                    })
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-fee-tax-table');
    }
}
