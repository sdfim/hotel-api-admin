<?php

namespace Modules\HotelContentRepository\Livewire\ProductAgeRestriction;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\AgeRestrictionTypeEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionTable extends Component implements HasForms, HasTable
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
        $this->title = 'Age Restriction for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Select::make('restriction_type')
                ->label('Age Restriction')
                ->options(function () {
                    $existingRestrictions = ProductAgeRestriction::where('product_id', $this->productId)
                        ->pluck('restriction_type')
                        ->toArray();

                    return collect(AgeRestrictionTypeEnum::cases())
                        ->pluck('value', 'value')
                        ->filter(fn($value) => !in_array($value, $existingRestrictions));
                })
                ->required(),
            TextInput::make('value')
                ->numeric()
                ->label('Value')
                ->required(),
            Checkbox::make('active')
                ->label('Active')
                ->default(true)
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAgeRestriction::where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('restriction_type')->label('Restriction Name'),
                TextColumn::make('value')->label('Value'),
                BooleanColumn::make('active')
                    ->label('Is Active'),
                ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-age-restriction-table');
    }
}
