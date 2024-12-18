<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\DaysPriorTypeEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationTable extends Component implements HasForms, HasTable
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
        $this->title = 'Deposit Information for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return  [
            Hidden::make('product_id')->default($this->productId),
            Select::make('days_prior_type')
                ->label('Type')
                ->options(array_combine(DaysPriorTypeEnum::values(), DaysPriorTypeEnum::values()))
                ->live()
                ->required(),
            TextInput::make('days')
                ->label('Days Prior')
                ->required()
                ->default(null)
                ->numeric()
                ->visible(fn($get) => $get('days_prior_type') !== DaysPriorTypeEnum::DATE->value),
            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->default(null)
                ->native(false)
                ->visible(fn($get) => $get('days_prior_type') === DaysPriorTypeEnum::DATE->value),
            Select::make('pricing_parameters')
                ->label('Pricing Parameters')
                ->options([
                    'per_channel' => 'Per Channel',
                    'per_room' => 'Per Room',
                    'per_rate' => 'Per Rate',
                ])
                ->required(),
            TextInput::make('pricing_value')
                ->numeric('decimal')
                ->label('Pricing Value'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductDepositInformation::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('days_prior_type')->label('Type')->searchable(),
                TextColumn::make('days')->label('Days Prior')->searchable(),
                TextColumn::make('date')->label('Date')->searchable(),
                TextColumn::make('pricing_parameters')->label('Pricing Parameters')->searchable(),
                TextColumn::make('pricing_value')->label('Value')->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-deposit-information-table');
    }
}
