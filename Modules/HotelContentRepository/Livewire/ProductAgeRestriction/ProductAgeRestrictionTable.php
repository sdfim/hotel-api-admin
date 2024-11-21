<?php

namespace Modules\HotelContentRepository\Livewire\ProductAgeRestriction;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Checkbox;
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
use Livewire\Component;
use Modules\Enums\AgeRestrictionTypeEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionTable extends Component implements HasForms, HasTable
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
            Select::make('restriction_type')
                ->label('Age Restriction')
                ->options(collect(AgeRestrictionTypeEnum::cases())->pluck('value', 'value'))
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
                TextColumn::make('restrictionType.name')->label('Restriction Name')->searchable(),
                TextColumn::make('restrictionType.description')->label('Description')->searchable(),
                TextColumn::make('value')->label('Value')->searchable(),
                BooleanColumn::make('active')
                    ->label('Is Active')
                    ->searchable(),            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Restriction')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        return [
                            'product_id' => $record->product_id,
                            'restriction_type' => $record->restriction_type,
                            'value' => $record->value,
                            'active' => $record->active,
                        ];
                    }),
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
                    ->tooltip('Add New Restriction')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        ProductAgeRestriction::create([
                            'product_id' => $data['product_id'],
                            'restriction_type' => $data['restriction_type'],
                            'value' => $data['value'],
                            'active' => $data['active'],
                        ]);

                        // Optionally, return a success message or perform additional operations
                        session()->flash('success', 'New restriction added successfully.');
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-age-restriction-table');
    }
}
