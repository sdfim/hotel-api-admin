<?php

namespace Modules\HotelContentRepository\Livewire\ProductAttributes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributesTable extends Component implements HasForms, HasTable
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
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('product_id')
                ->label('Product')
                ->options(Product::pluck('name', 'id'))
                ->disabled(fn () => $this->productId)
                ->required(),
            Select::make('config_attribute_id')
                ->label('Attribute')
                ->options(ConfigAttribute::all()->pluck('name', 'id'))
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAttribute::where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('attribute.name')->label('Attribute Name'),
//                TextColumn::make('attribute.default_value')->label('Value'),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Attribute')
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
                        ProductAttribute::create($data);
                    })
                    ->tooltip('Add New Attribute')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-attributes-table');
    }
}
