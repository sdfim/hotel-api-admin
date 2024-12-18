<?php

namespace Modules\HotelContentRepository\Livewire\ProductAttributes;

use App\Helpers\ClassHelper;
use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributesTable extends Component implements HasForms, HasTable
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
        $this->title = 'Attributes for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Select::make('config_attribute_id')
                ->label('Attribute')
                ->options(ConfigAttribute::all()->pluck('name', 'id'))
                ->createOptionForm(AttributesForm::getSchema())
                ->createOptionUsing(function (array $data) {
                    $data['default_value'] = '';
                    $attribute = ConfigAttribute::create($data);
                    Notification::make()
                        ->title('Attributes created successfully')
                        ->success()
                        ->send();
                    return $attribute->id;
                })
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
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-attributes-table');
    }
}
