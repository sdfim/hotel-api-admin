<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\Image\AddImage;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\Product;

class HotelImagesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $productId = null;

    public ?int $roomId = null;

    public string $viewMode = 'list';

    public function mount(?int $productId, ?int $roomId = null): void
    {
        $this->productId = $productId;
        $this->roomId = $roomId;
        if ($this->roomId) {
            $this->viewMode = 'grid';
        }
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'list' ? 'grid' : 'list';
    }

    public static function generateGalleryDetails(Product $product): array
    {
        $type = ucfirst($product->product_type);
        $destination = $product->related?->giataCode->locale ?? 'Unknown';
        $vendor = $product->vendor->name;
        $giataCode = $product->related?->giata_code ?? 'Unknown';
        $filePath = "{$type}_{$giataCode}";
        $galleryName = $description = "{$type} - {$destination} - {$vendor} - {$giataCode}";

        return compact('filePath', 'galleryName', 'description');
    }

    public function table(Table $table): Table
    {
        $filePath = $galleryName = $description = '';
        $product = $room = null;
        if ($this->productId) {
            $product = Product::find($this->productId);
            ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = self::generateGalleryDetails($product);
            $description = 'Product Image Gallery: '.$galleryName;
        }
        if ($this->roomId) {
            $product = Hotel::whereHas('rooms', function ($query) {
                $query->where('id', $this->roomId);
            })->first()->product;
            $room = HotelRoom::find($this->roomId);
            ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = $this->generateGalleryDetails($product);
            $filePath = $filePath."/Room_{$this->roomId}";
            $galleryName = $galleryName." - Room {$this->roomId}";
            $description = 'Room Image Gallery: '.$galleryName;
        }

        return $table
            ->paginated([5, 10, 25, 50, 100])
            ->query(function () {
                $query = Image::query();
                if ($this->productId) {
                    $query->whereHas('galleries', function ($query) {
                        $query->whereHas('products', function ($query) {
                            $query->where('product_id', $this->productId);
                        });
                    });
                }
                if ($this->roomId) {
                    $query->whereHas('galleries', function ($query) {
                        $query->whereHas('hotelRooms', function ($query) {
                            $query->where('id', $this->roomId);
                        });
                    });
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['md' => 3, 'xl' => 4, '2xl' => 5])
            ->columns($this->viewMode === 'list' ? $this->getListViewColumns() : $this->getGridViewColumns())
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->form(HotelImagesForm::getFormComponents())
                    ->modalHeading('Edit Image')
                    ->fillForm(fn (Image $record) => $record->attributesToArray())
                    ->visible(fn (Image $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (Image $record) => $record->delete())
                    ->visible(fn (Image $record) => Gate::allows('delete', $record))
                    ->after(fn (Image $record) => Storage::disk('public')->delete($record->image_url)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->createAnother(false)
                    ->modalHeading('Create Image')
                    ->form(array_filter(
                        HotelImagesForm::getFormComponents($filePath),
                        fn ($component) => ! (($this->roomId || $this->productId) && $component instanceof Select && $component->getName() === 'galleries')
                    ))
                    ->action(function ($data) use ($product, $room, $galleryName, $description) {
                        /** @var AddImage $addImage */
                        $addImage = app(AddImage::class);
                        $addImage->addImageToGallery($data, $product, $room, $galleryName, $description);
                    })
                    ->visible(fn () => Gate::allows('create', Image::class)),

                Tables\Actions\Action::make('toggleViewMode')
                    ->label('Toggle View Mode')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(fn () => $this->toggleViewMode())
                    ->icon($this->viewMode === 'list' ? 'heroicon-o-table-cells' : 'heroicon-o-cube-transparent')
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotel-images.hotel-images-table');
    }

    protected function getListViewColumns(): array
    {
        return [
            ImageColumn::make('image_url')
                ->size('100px')
                ->getStateUsing(function ($record) {
                    $validAlts = ['Header image', 'Thumbnail image', 'Gallery image'];
                    if (in_array($record->alt, $validAlts)) {
                        return url(env('CRM_PATH_HOTEL_IMAGES', '').$record->image_url);
                    }
                    if ($record->alt === 'Room image') {
                        return url(env('CRM_PATH_ROOM_IMAGES', '').$record->image_url);
                    }

                    return $record->image_url;
                }),
            TextColumn::make('tag')
                ->searchable(),
            TextColumn::make('alt')
                ->searchable(),
            TextColumn::make('section.name')
                ->searchable(),
            TextColumn::make('galleries.gallery_name')
                ->searchable()
                ->badge()
                ->color('gray'),
        ];
    }

    protected function getGridViewColumns(): array
    {
        return [
            Tables\Columns\Layout\Grid::make()
                ->columns(1)
                ->schema([
                    ImageColumn::make('image_url')
                        ->size($this->viewMode === 'list' ? '200px' : '100%')
                        ->getStateUsing(function ($record) {
                            $validAlts = ['Header image', 'Thumbnail image', 'Gallery image'];
                            if (in_array($record->alt, $validAlts)) {
                                return url(env('CRM_PATH_HOTEL_IMAGES', '').$record->image_url);
                            }
                            if ($record->alt === 'Room image') {
                                return url(env('CRM_PATH_ROOM_IMAGES', '').$record->image_url);
                            }

                            return $record->image_url;
                        }),
//                    TextColumn::make('tag')
//                        ->searchable(),
//                    TextColumn::make('alt')
//                        ->searchable(),
//                    TextColumn::make('section.name')
//                        ->searchable(),
                    TextColumn::make('galleries.gallery_name')
                        ->searchable()
                    ->wrap(),
                ]),
        ];
    }

    protected function modifyQuery($query)
    {
        return $query->when($this->record->exists, function ($query) {
            return $query->whereHas('galleries', fn ($query) => $query->where('gallery_id', $this->record->id));
        })
            ->when(! $this->record->exists, function ($query) {
                return $query->whereIn('id', $this->imageIds);
            });
    }
}
