<?php

namespace Modules\HotelContentRepository\Livewire\ProductContactInformation;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\Grid;
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
use Livewire\Component;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class ProductContactInformationTable extends Component implements HasForms, HasTable
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
            Grid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label('First Name')
                        ->required(),
                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->required(),
                ]),
            Grid::make(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email(),
                    TextInput::make('phone')
                        ->label('Phone'),
                ]),
            Select::make('contactInformations')
                ->label('Job Descriptions')
                ->multiple()
                ->options(ConfigJobDescription::pluck('name', 'id')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductContactInformation::with('contactInformations')->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('first_name')->label('First Name')->searchable(),
                TextColumn::make('last_name')->label('Last Name')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Phone')->searchable(),
                TextColumn::make('contactInformations')
                    ->label('Job Descriptions')
                    ->formatStateUsing(function ($record) {
                        return $record->contactInformations->pluck('name')->join(', ');
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Contact Information')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['contactInformations'] = $record->contactInformations->pluck('id')->toArray();
                        return $data;
                    })
                    ->action(function ($data, $record) {
                        $contactInformations = $data['contactInformations'] ?? [];
                        unset($data['contactInformations']);

                        $record->update($data);
                        $record->contactInformations()->sync($contactInformations);
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
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        $contactInformations = $data['contactInformations'] ?? [];
                        unset($data['contactInformations']);

                        $hotelContactInformation = ProductContactInformation::create($data);
                        $hotelContactInformation->contactInformations()->sync($contactInformations);
                    })
                    ->tooltip('Add New Contact Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-contact-information-table');
    }
}
