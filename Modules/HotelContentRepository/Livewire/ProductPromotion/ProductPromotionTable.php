<?php

namespace Modules\HotelContentRepository\Livewire\ProductPromotion;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductPromotion;
use Filament\Tables\Contracts\HasTable;
use Modules\HotelContentRepository\Models\ImageGallery;

class ProductPromotionTable extends Component implements HasForms, HasTable
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
        $this->title = 'Promotions for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return  [
            Hidden::make('product_id')->default($this->productId),
            TextInput::make('promotion_name')
                ->label('Promotion Name')
                ->required(),
            Textarea::make('description')
                ->label('Description'),
            Grid::make()
                ->schema([
                    DatePicker::make('validity_start')
                        ->label('Validity Start')
                        ->native(false)
                        ->required(),
                    DatePicker::make('validity_end')
                        ->label('Validity End')
                        ->native(false)
                        ->required(),
                ]),
            Grid::make()
                ->schema([
                    TextInput::make('min_night_stay')
                        ->label('Min Night Stay')
                        ->numeric()
                        ->required(),
                    TextInput::make('max_night_stay')
                        ->label('Max Night Stay')
                        ->numeric()
                        ->required(),
                ]),
            Grid::make()
                ->schema([
                    DatePicker::make('booking_start')
                        ->label('Booking Start')
                        ->native(false)
                        ->required(),
                    DatePicker::make('booking_end')
                        ->label('Booking End')
                        ->native(false)
                        ->required(),
                ]),
            Textarea::make('terms_conditions')
                ->label('Terms & Conditions'),
            Textarea::make('exclusions')
                ->label('Exclusions'),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->searchable()
                ->options(ImageGallery::pluck('gallery_name', 'id')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductPromotion::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('promotion_name')->label('Promotion Name')->searchable(),
                TextColumn::make('description')->label('Description')->searchable(),
                TextColumn::make('validity_start')->label('Validity Start')->date(),
                TextColumn::make('validity_end')->label('Validity End')->date(),
                TextColumn::make('booking_start')->label('Booking Start')->date(),
                TextColumn::make('booking_end')->label('Booking End')->date(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-promotion-table');
    }
}
