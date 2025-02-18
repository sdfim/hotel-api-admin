<?php

namespace Modules\HotelContentRepository\Livewire\ProductFeeTaxes;

use App\Models\Supplier;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\API\Suppliers\Enums\HBSI\HbsiFeeTaxTypeEnum;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductFeeTaxApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public string $title;

    public ?string $supplierType = null;

    public function mount(Product $product, ?int $rateId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->title = 'Fees and Taxes for <h4>'.$product->name.'</h4>';
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
            Hidden::make('rate_id')->default($this->rateId),

            Fieldset::make('General settings')
                ->columns(2)
                ->schema([
                    Select::make('supplier_id')
                        ->label('Supplier/Driver')
                        ->required()
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
                            'edit' => 'Edit',
                            'delete' => 'Delete',
                        ])
                        ->live()
                        ->required(),
                    TextInput::make('name')
                        ->label('New Name')
                        ->reactive()
                        ->required()
                        ->visible(fn (Get $get) => $get('action_type') !== 'delete'),
                    Select::make('old_name')
                        ->label('Current Name')
                        ->reactive()
                        ->searchable()
                        ->options(fn (Get $get) => $get('old_name_options') ?? [])
                        ->required()
                        ->visible(fn (Get $get) => $get('action_type') !== 'add'),
                ]),

            Fieldset::make('Type settings')
                ->columns(2)
                ->visible(fn (Get $get) => $get('action_type') !== 'delete')
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            ProductFeeTaxTypeEnum::TAX->value => 'Tax',
                            ProductFeeTaxTypeEnum::FEE->value => 'Fee',
                        ])
                        ->required(),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('commissionable')
                                ->label('Commissionable')
                                ->inline(false)
                                ->required(),
                            Select::make('fee_category')
                                ->label('Fee Category')
                                ->options([
                                    'mandatory' => 'Mandatory',
                                    'optional' => 'Optional',
                                ])
                                ->required(),
                        ])->columnSpan(1),
                ]),
            Fieldset::make('Value settings')
                ->columns(2)
                ->visible(fn (Get $get) => $get('action_type') === 'add')
                ->schema([
                    Select::make('value_type')
                        ->label('Value Type')
                        ->options([
                            ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                            ProductFeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
                        ])
                        ->required(),
                    Select::make('apply_type')
                        ->label('Apply Type')
                        ->options([
                            ProductFeeTaxApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                            ProductFeeTaxApplyTypeEnum::PER_PERSON->value => 'Per Person',
                            ProductFeeTaxApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                        ])
                        ->required(),

                    TextInput::make('net_value')
                        ->label('Net Value')
                        ->numeric(2)
                        ->required(),
                    TextInput::make('rack_value')
                        ->label('Rack Value')
                        ->numeric(2)
                        ->required(),
                ]),
            Grid::make(2)
                ->schema([
                    Select::make('collected_by')
                        ->label('Collected By')
                        ->options([
                            FeeTaxCollectedByEnum::DIRECT->value => 'Direct',
                            FeeTaxCollectedByEnum::VENDOR->value => 'Vendor',
                        ])
                        ->required(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductFeeTax::query()->where('product_id', $this->productId)
                    ->where('rate_id', $this->rateId))
            ->columns([
                TextColumn::make('old_name')->label('Current Name')->searchable(),
                TextColumn::make('name')->label('New Name')->searchable(),
                TextColumn::make('supplier.name')->label('Driver')->searchable(),
                TextColumn::make('action_type')
                    ->label('Action Type')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'primary' => 'add',
                        'warning' => 'edit',
                        'danger' => 'delete',
                    ]),

                TextColumn::make('type')->label('Type')->sortable(),
                TextColumn::make('net_value')->label('Net')->sortable(),
                TextColumn::make('rack_value')->label('Rack')->sortable(),
                IconColumn::make('value_type')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'heroicon-o-receipt-percent',
                        ProductFeeTaxValueTypeEnum::AMOUNT->value => 'heroicon-o-currency-dollar',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('apply_type')->label('Apply Type')->sortable(),
                IconColumn::make('commissionable')
                    ->label('Commissionable')
                    ->boolean(),

                TextColumn::make('fee_category')->label('Fee Category'),
                TextColumn::make('collected_by')->label('Collected By'),

                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-fee-tax-table');
    }
}
