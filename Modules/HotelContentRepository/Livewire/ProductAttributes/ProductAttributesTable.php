<?php

namespace Modules\HotelContentRepository\Livewire\ProductAttributes;

use App\Actions\ConfigAttribute\CreateConfigAttribute;
use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributesTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public string $title;

    public function mount(Product $product)
    {
        $this->productId = $product->id;
        $this->title = 'Attributes for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Select::make('config_attribute_id')
                ->label('Attribute')
                ->searchable()
                ->options(
                    ConfigAttribute::with('categories')
                        ->get()
                        ->sortBy('name')
                        ->mapWithKeys(function ($attribute) {
                            $categories = $attribute->categories
                                ->pluck('name')
                                ->map(fn ($name) => \Illuminate\Support\Str::of($name)->replace('_', ' ')->title())
                                ->join(', ');

                            $categories = $categories ? " | categories: $categories" : null;

                            return [$attribute->id => $attribute->name.($categories ? "{$categories}" : '')];
                        })
                )
                ->createOptionForm(Gate::allows('create', ConfigAttribute::class) ? AttributesForm::getSchema() : [])
                ->createOptionUsing(function (array $data) {
                    $data['default_value'] = '';
                    /** @var CreateConfigAttribute $createConfigAttribute */
                    $createConfigAttribute = app(CreateConfigAttribute::class);
                    $attribute = $createConfigAttribute->create($data);
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
            ->deferLoading()
            ->columns([
                TextColumn::make('attribute.name')
                    ->label('Attribute Name')
                    ->searchable(),
                TextColumn::make('attribute.categories.name')
                    ->searchable()
                    ->label('Categories')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
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
