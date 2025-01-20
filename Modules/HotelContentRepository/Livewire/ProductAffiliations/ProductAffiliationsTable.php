<?php

namespace Modules\HotelContentRepository\Livewire\ProductAffiliations;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use App\Livewire\Configurations\Amenities\AmenitiesForm;
use App\Livewire\Configurations\JobDescriptions\JobDescriptionsForm;
use App\Models\Configurations\ConfigAmenity;
use App\Models\Configurations\ConfigConsortium;
use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationsTable extends Component implements HasForms, HasTable
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
        $this->title = 'Amenities for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function schemeForm(): array
    {
        return  [
            Hidden::make('product_id')->default($this->productId),

            Grid::make(1)->schema([
                Select::make('consortia_id')
                    ->label('Consortia')
                    ->options(ConfigConsortium::pluck('name', 'id'))
                    ->required(),
            ]),
            Grid::make(2)->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false)
                    ->required(),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false)
                    ->required(),
            ]),
            Grid::make(1)->schema([
                Textarea::make('description')
                    ->label('Description')
                    ->required(),
            ]),
            Grid::make(1)
                ->schema([
                    Select::make('amenities')
                    ->label('Amenities')
                    ->options(ConfigAmenity::pluck('name', 'name'))
                    ->multiple()
                        ->createOptionForm(AmenitiesForm::getSchema())
                        ->createOptionUsing(function (array $data) {
                            $amenity = ConfigAmenity::create($data);
                            Notification::make()
                                ->title('Department created successfully')
                                ->success()
                                ->send();
                            return $amenity->name;
                        })
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAffiliation::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('consortia.name')->label('Consortia'),
                TextColumn::make('description')->label('Description')->wrap(),
                TextColumn::make('start_date')->label('Start Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('amenities')->label('Amenities')->wrap(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-affiliations-table');
    }
}
