<?php

namespace Modules\HotelContentRepository\Livewire\ProductInformativeServices;


use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigServiceType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServicesTable extends Component implements HasForms, HasTable
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
        $this->title = 'Informational Service for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Select::make('service_id')
                ->label('Service Type')
                ->options(ConfigServiceType::all()->pluck('name', 'id')->toArray())
                ->required(),
            TextInput::make('cost')
                ->label('Cost')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductInformativeService::with('service')->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('service.name')->label('Service Type')->searchable(),
                TextColumn::make('service.description')->label('Description')->searchable(),
                TextColumn::make('cost')->label('Cost')->searchable(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-informative-services-table');
    }
}
