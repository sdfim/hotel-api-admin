<?php

namespace Modules\HotelContentRepository\Livewire\ProductFeeTaxes;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductFeeTaxApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
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

    public function mount(Product $product, ?int $rateId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->title = 'Fees and Taxes for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),

            TextInput::make('name')->label('Name')->required(),
            Grid::make(3)
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            ProductFeeTaxTypeEnum::TAX->value => 'Tax',
                            ProductFeeTaxTypeEnum::FEE->value => 'Fee',
                        ])
                        ->required(),
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
                            ProductFeeTaxApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Person Per Person',
                        ])
                        ->required(),
                ]),
            Grid::make(3)
                ->schema([
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
                    Select::make('fee_category')
                        ->label('Fee Category')
                        ->options([
                            'mandatory' => 'Mandatory',
                            'optional' => 'Optional',
                        ])
                        ->required(),
                ]),
            Toggle::make('commissionable')
                ->label('Commissionable')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductFeeTax::query()->where('product_id', $this->productId)
                    ->where('rate_id', $this->rateId))
            ->columns([
                TextInputColumn::make('name')->label('Name')->searchable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                TextInputColumn::make('net_value')
                    ->label('Net Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/'])
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                TextInputColumn::make('rack_value')
                    ->label('Rack Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/'])
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                SelectColumn::make('type')
                    ->label('Type')
                    ->options([
                        ProductFeeTaxTypeEnum::TAX->value => 'Tax',
                        ProductFeeTaxTypeEnum::FEE->value => 'Fee',
                    ])
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                SelectColumn::make('value_type')
                    ->label('Value Type')
                    ->options([
                        ProductFeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                        ProductFeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
                    ])
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                SelectColumn::make('apply_type')
                    ->label('Apply Type')
                    ->options([
                        ProductFeeTaxApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                        ProductFeeTaxApplyTypeEnum::PER_PERSON->value => 'Per Person',
                        ProductFeeTaxApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Person Per Person',
                    ])
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                SelectColumn::make('collected_by')
                    ->label('Collected By')
                    ->options([
                        FeeTaxCollectedByEnum::DIRECT->value => 'Direct',
                        FeeTaxCollectedByEnum::VENDOR->value => 'Vendor',
                    ])
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                SelectColumn::make('fee_category')
                    ->label('Fee Category')
                    ->options([
                        'mandatory' => 'Mandatory',
                        'optional' => 'Optional',
                    ])
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

                ToggleColumn::make('commissionable')
                    ->label('Commissionable')
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Product::class)),

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
