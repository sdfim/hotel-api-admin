<?php

namespace Modules\HotelContentRepository\Livewire\ProductAffiliations;

use App\Actions\ConfigAmenity\CreateConfigAmenity;
use App\Livewire\Configurations\Amenities\AmenitiesForm;
use App\Models\Configurations\ConfigAmenity;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationsTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public ?int $roomId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->roomId = $roomId;
        $this->title = 'Amenities for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),
            Hidden::make('room_id')->default($this->roomId),

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
                            /** @var CreateConfigAmenity $createConfigAmenity */
                            $createConfigAmenity = app(CreateConfigAmenity::class);
                            $amenity = $createConfigAmenity->create($data);
                            Notification::make()
                                ->title('Department created successfully')
                                ->success()
                                ->send();

                            return $amenity->name;
                        }),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAffiliation::query()->where('product_id', $this->productId)
                    ->where('rate_id', $this->rateId)
                    ->where('room_id', $this->roomId)
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
