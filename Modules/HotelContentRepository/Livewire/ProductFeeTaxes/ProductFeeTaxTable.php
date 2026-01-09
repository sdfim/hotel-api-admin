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
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelAdapter;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use Modules\Utils\Tools;

class ProductFeeTaxTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public Product $product;

    public ?int $rateId = null;

    public ?int $roomId = null;

    public ?array $rateRoomIds = [];

    public string $title;

    public ?string $supplierType = null;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null): void
    {
        $this->productId = $product->id;
        $this->product = $product;
        $this->title = 'Fees and Taxes for '.$product->name;
    }

    public function updatedSupplierId($value): void
    {
        $supplier = Supplier::find($value);
        $this->supplierType = $supplier ? $supplier->name : null;
    }

    protected function getSupplierFeeTaxOptions(): array
    {
        $supplierTaxOptions = [];
        if ($this->supplierType === SupplierNameEnum::HBSI->value) {
            $hbsiService = app(HbsiHotelAdapter::class);
            $supplierTaxOptions = $hbsiService->getTaxOptions($this->product->related->giata_code);
            $supplierTaxOptions = array_combine($supplierTaxOptions, $supplierTaxOptions);
        }

        // TODO: $docTaxOptions - Options from the manual document. Can be removed if not needed.
        //        $docTaxOptions = match ($this->supplierType) {
        //            SupplierNameEnum::HBSI->value => collect(HbsiFeeTaxTypeEnum::cases())
        //                ->mapWithKeys(fn ($enum) => [$enum->value => $enum->value])
        //                ->toArray(),
        //            default => [],
        //        };
        //        $supplierTaxOptions = array_merge($supplierTaxOptions, $docTaxOptions);

        ksort($supplierTaxOptions);

        return $supplierTaxOptions;
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
                        'included' => 'Hilton Tax Inclusive',
                        'informative' => 'This information is only displayed in the response under the "informative_fees" key',
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
                        ->required(fn (Get $get) => ! in_array($get('action_type'), ['informative']))
                        ->options(
                            Supplier::query()
                                ->whereJsonContains('product_type', 'hotel')
                                ->whereIn('name', SupplierNameEnum::pricingList())
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
                            //                            'vat' => 'VAT management',
                            //                            'included' => 'Hilton inclusive',
                            'informative' => 'Informative',
                        ])
                        ->live()
                        ->rules(['required']),
                    TextInput::make('name')
                        ->label(fn (Get $get) => $get('action_type') === 'informative' ? 'Description' : 'New Name')
                        ->reactive()
                        ->visible(fn (Get $get) => ! in_array($get('action_type'), ['vat'])),
                    TextInput::make('old_name')
                        ->label('Current Name')
                        ->datalist(fn (Get $get) => array_values($get('old_name_options') ?? []))
                        ->autocomplete('list')
                        ->visible(fn (Get $get) => ! in_array($get('action_type'), ['add', 'vat', 'informative', 'included']))
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
                ->visible(fn (Get $get) => ! in_array($get('action_type'), ['vat', 'included'])),

            Fieldset::make('Type Setting')
                ->columns()
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            ProductFeeTaxTypeEnum::TAX->value => 'Tax',
                            ProductFeeTaxTypeEnum::FEE->value => 'Fee',
                        ]),
                    Grid::make()
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
                ->visible(fn (Get $get) => ! in_array($get('action_type'), ['informative', 'vat', 'included'])),

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
                ->visible(fn (Get $get) => ! in_array($get('action_type'), ['vat', 'included'])),

            Fieldset::make('Value Setting')
                ->columns(3)
                ->visible(fn (Get $get) => in_array($get('action_type'), ['add', 'edit', 'vat', 'informative', 'included']))
                ->schema([
                    Select::make('value_type')
                        ->label('Value Type')
                        ->options(function (Get $get) {
                            if ($get('action_type') === 'vat') {
                                return [
                                    ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                                ];
                            } else {

                                return [
                                    ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                                    ProductFeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
                                ];
                            }
                        })
                        ->required(),
                    Select::make('apply_type')
                        ->label('Apply Type')
                        ->options([
                            ProductApplyTypeEnum::PER_ROOM->value => 'Per Room',
                            ProductApplyTypeEnum::PER_PERSON->value => 'Per Person',
                            ProductApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                        ])
                        ->reactive()
                        ->required()
                        ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

                    Select::make('currency')
                        ->label('Currency')
                        ->searchable()
                        ->required()
                        ->options(Tools::getCurrencyOptions())
                        ->visible(fn (Get $get) => $get('action_type') !== 'vat'),

                    TextInput::make('net_value')
                        ->label('Net Value')
                        ->numeric(2)
                        ->reactive()
                        ->required()
                        ->rules(['required']),
                ]),
            Grid::make()
                ->schema([
                    Select::make('collected_by')
                        ->label('Collected By')
                        ->options([
                            FeeTaxCollectedByEnum::DIRECT->value => 'Direct',
                            FeeTaxCollectedByEnum::VENDOR->value => 'Vendor',
                        ]),
                ])
                ->visible(fn (Get $get) => ! in_array($get('action_type'), ['vat', 'included'])),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductFeeTax::query()
                    ->where('product_id', $this->productId)
            )
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
                            $record->room_id !== null => $record->room?->external_code,
                            default => '',
                        };
                    }),
                TextColumn::make('supplier.name')
                    ->label('Driver')
                    ->searchable(),
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
                        'info' => 'informative',
                    ]),
                TextColumn::make('old_name')
                    ->label('Current Name')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('New Name')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')->label('Type')->sortable(),
                TextColumn::make('net_value')->label('Net')->sortable(),
                IconColumn::make('value_type')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'heroicon-o-percent-badge',
                        ProductFeeTaxValueTypeEnum::AMOUNT->value => 'heroicon-o-currency-dollar',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('apply_type')
                    ->label('Apply Type')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $applyType = $record->apply_type;
                        if ($applyType instanceof ProductApplyTypeEnum) {
                            return $applyType->label();
                        }

                        return ProductApplyTypeEnum::tryFrom($applyType)?->label() ?? $applyType;
                    }),
                IconColumn::make('commissionable')
                    ->label(new HtmlString('<span title="Commissionable to Travel Agent">Comm.<br>to TA</span>'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record?->action_type !== 'informative' ? $record->commissionable : null),

                TextColumn::make('fee_category')->label('Fee Category'),
                TextColumn::make('collected_by')->label('Collected By'),

                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->filters([
                SelectFilter::make('action_type')
                    ->label('Action Type')
                    ->options([
                        'add' => 'Add',
                        'update' => 'Update',
                        'edit' => 'Edit',
                        'delete' => 'Delete',
                        'vat' => 'VAT management',
                        'informative' => 'Informative',
                    ]),
                Filter::make('level')
                    ->label('Level')
                    ->form([
                        Select::make('value')
                            ->label('Level')
                            ->options([
                                'hotel' => 'Hotel',
                                'rate' => 'Rate',
                                'room' => 'Room',
                            ])
                            ->placeholder('Select level'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! $data['value']) {
                            return;
                        }

                        match ($data['value']) {
                            'hotel' => $query->whereNull('rate_id')->whereNull('room_id'),
                            'rate' => $query->whereNotNull('rate_id')->whereNull('room_id'),
                            'room' => $query->whereNotNull('room_id'),
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return $data['value']
                            ? 'Level: '.ucfirst($data['value'])
                            : null;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form($this->schemeForm())
                        ->modalWidth('6xl')
                        ->action(function (ProductFeeTax $record, array $data) {
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
                            }
                            if ($data['action_type'] === 'add') {
                                // General Setting
                                $data['old_name'] = null;
                            }
                            if ($data['action_type'] === 'included') {
                                $data['collected_by'] = 'Direct';
                            }
                            if ($data['action_type'] === 'update') {
                                // Value Setting
                                $data['value_type'] = null;
                                $data['apply_type'] = null;
                                $data['net_value'] = null;
                            }
                            if ($data['net_value']) {
                                $data['rack_value'] = $data['net_value'];
                            }
                            $record->update($data);
                        })
                        ->closeModalByClickingAway(false)
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    Action::make('clone')
                        ->label('Clone')
                        ->icon('heroicon-o-clipboard-document')
                        ->color('warning')
                        ->modalHeading(new HtmlString("Clone $this->title"))
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
                        ->visible(fn (ProductFeeTax $record) => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])
                    ->visible(
                        fn (ProductFeeTax $record): bool => ($this->rateId && $this->rateId === $record->rate_id)
                        || ($this->roomId && $this->roomId === $record->room_id)
                        || (! $this->rateId && ! $this->roomId)
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->createAnother(false)
                    ->modalWidth('6xl')
                    ->tooltip('Add New Entity')
                    ->icon('heroicon-o-plus')
                    ->visible(fn () => Gate::allows('create', Product::class))
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function ($data) {
                        if ($data['action_type'] === 'delete') {
                            $data['name'] = null;
                            $data['type'] = null;
                            $data['fee_category'] = null;
                            $data['value_type'] = null;
                            $data['apply_type'] = null;
                            $data['net_value'] = null;
                        }
                        if ($data['action_type'] === 'add') {
                            $data['old_name'] = null;
                        }
                        if ($data['action_type'] === 'included') {
                            $data['collected_by'] = 'Direct';
                        }
                        if ($data['action_type'] === 'update') {
                            $data['value_type'] = null;
                            $data['apply_type'] = null;
                            $data['net_value'] = null;
                        }
                        if ($data['net_value']) {
                            $data['rack_value'] = $data['net_value'];
                        }
                        ProductFeeTax::create($data);
                    }),
            ]);
    }

    public function render(): View|Factory|Application
    {
        return view('livewire.products.product-fee-tax-table');
    }
}
