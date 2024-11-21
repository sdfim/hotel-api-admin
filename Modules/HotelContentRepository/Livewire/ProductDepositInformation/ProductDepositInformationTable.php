<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\ClassHelper;
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
use Livewire\Component;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationTable extends Component implements HasForms, HasTable
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
        return  [
            Select::make('product_id')
                ->label('Product')
                ->options(Product::pluck('name', 'id'))
                ->disabled(fn () => $this->productId)
                ->required(),
            TextInput::make('days_departure')
                ->label('Days Prior to Departure')
                ->required()
                ->numeric(),
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
                TextColumn::make('days_departure')->label('Days Prior to Departure')->searchable(),
                TextColumn::make('pricing_parameters')->label('Pricing Parameters')->searchable(),
                TextColumn::make('pricing_value')->label('Value')->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Deposit Information')
                    ->form($this->schemeForm())
                    ->modalHeading('Edit Deposit Information'),
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
                        ProductDepositInformation::create($data);
                    })
                    ->tooltip('Add New Deposit Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-deposit-information-table');
    }
}
