<?php

namespace Modules\HotelContentRepository\Livewire\ProductFeeTaxes;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\FeeTaxTypeEnum;
use Modules\Enums\FeeTaxValueTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public function mount(int $productId)
    {
        $this->productId = $productId;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('product_id')
                ->label('Product')
                ->options(Product::pluck('name', 'id'))
                ->disabled(fn () => $this->productId)
                ->required(),
            TextInput::make('name')->label('Name')->required(),
            Grid::make(2)
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            FeeTaxTypeEnum::TAX->value => 'Tax',
                            FeeTaxTypeEnum::FEE->value => 'Fee',
                        ])
                        ->required(),
                    Select::make('value_type')
                        ->label('Value Type')
                        ->options([
                            FeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                            FeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
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
                    TextInput::make('tax')
                        ->label('Tax')
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
            )
            ->columns([
                TextInputColumn::make('name')->label('Name')->searchable(),

                TextInputColumn::make('net_value')
                    ->label('Net Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                TextInputColumn::make('rack_value')
                    ->label('Rack Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                TextInputColumn::make('tax')
                    ->label('Tax')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                SelectColumn::make('type')
                    ->label('Type')
                    ->options([
                        FeeTaxTypeEnum::TAX->value => 'Tax',
                        FeeTaxTypeEnum::FEE->value => 'Fee',
                    ])
                    ->sortable(),
                SelectColumn::make('value_type')
                    ->label('Value Type')
                    ->options([
                        FeeTaxValueTypeEnum::PERCENTAGE->value => 'Percentage',
                        FeeTaxValueTypeEnum::AMOUNT->value => 'Amount',
                    ])
                    ->sortable(),
                SelectColumn::make('collected_by')
                    ->label('Collected By')
                    ->options([
                        FeeTaxCollectedByEnum::DIRECT->value => 'Direct',
                        FeeTaxCollectedByEnum::VENDOR->value => 'Vendor',
                    ])
                    ->sortable(),
                SelectColumn::make('fee_category')
                    ->label('Fee Category')
                    ->options([
                        'mandatory' => 'Mandatory',
                        'optional' => 'Optional',
                    ])
                    ->sortable(),
                ToggleColumn::make('commissionable')
                    ->label('Commissionable')
                    ->sortable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Fee Tax')
                    ->form($this->schemeForm()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->productId ? ['product_id' => $this->productId] : [];
                    })
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        ProductFeeTax::create($data);
                    })
                    ->tooltip('Add New Fee')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-fee-tax-table');
    }

    public function save($record)
    {
        $this->validate([
            'net_value' => 'required|numeric',
            'rack_value' => 'required|numeric',
            'tax' => 'required|numeric',
        ]);

        $record->update($this->getValidatedData());

        session()->flash('message', 'Data saved successfully.');
    }

    protected function getValidatedData(): array
    {
        return [
            'net_value' => $this->net_value,
            'rack_value' => $this->rack_value,
            'tax' => $this->tax,
        ];
    }
}
