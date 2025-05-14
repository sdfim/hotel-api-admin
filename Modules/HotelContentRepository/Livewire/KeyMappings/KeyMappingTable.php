<?php

namespace Modules\HotelContentRepository\Livewire\KeyMappings;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use Modules\HotelContentRepository\Models\Product;

class KeyMappingTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public string $title;

    public function mount(Product $product)
    {
        $this->productId = $product->id;
        $this->title = 'External Identifiers for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Select::make('key_mapping_owner_id')
                ->label('Key Mapping Owner')
                ->options(KeyMappingOwner::pluck('name', 'id'))
                ->relationship('keyMappingOwner', 'name')
                ->createOptionForm(Gate::allows('create', KeyMappingOwner::class) ? [
                    TextInput::make('name')
                        ->label('Name')
                        ->required(),
                ] : [])
                ->required(),
            TextInput::make('key_id')
                ->label('Key ID')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KeyMapping::with('keyMappingOwner')->where('product_id', $this->productId)
            )
            ->columns([
                TextInputColumn::make('key_id')
                    ->label('External ID')
                    ->searchable(),
                TextColumn::make('keyMappingOwner.name')
                    ->label('External Owner'),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.key-mapping-table');
    }
}
