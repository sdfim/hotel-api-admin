<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use App\Helpers\ClassHelper;
use App\Models\ExpediaContentSlave;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\ImageSourceEnum;
use Modules\HotelContentRepository\Actions\Image\AddImage;
use Modules\HotelContentRepository\Actions\Image\EditImage;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageSection;
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
            // Get Expedia supplier code if available
            $roomCode = $this->roomId; // Default fallback to room ID
            if ($room && ! empty($room->supplier_codes)) {
                $supplierCodes = collect(json_decode($room->supplier_codes, true) ?? []);
                $expediaCode = $supplierCodes->firstWhere('supplier', 'Expedia');
                if ($expediaCode && isset($expediaCode['code'])) {
                    $roomCode = $expediaCode['code'];
                }
            }
            $galleryName = $galleryName." - Room {$roomCode}";
            $description = 'Room Image Gallery: '.$galleryName;
        }

        if ($this->viewMode != 'list') {
            $table = $table->contentGrid(['md' => 3, 'xl' => 4, '2xl' => 5]);
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
            ->columns($this->viewMode === 'list' ? $this->getListViewColumns() : $this->getGridViewColumns())
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->modalWidth('6xl')
                    ->form(HotelImagesForm::getFormComponents('', $this->roomId ? 'room' : '', true))
                    ->modalHeading('Edit Image')
                    ->fillForm(function (Image $record) {
                        $attributes = $record->attributesToArray();
                        $attributes['tags'] = $record->tag ? explode(';', $record->tag) : [];

                        return $attributes;
                    })
                    ->action(function ($data, \Filament\Forms\Form $form, Image $record) {
                        // Convert tags array to string for tag field
                        $data['tag'] = isset($data['tags']) ? implode(';', $data['tags']) : '';
                        unset($data['tags']);
                        $galleries = $form->getRawState()['galleries'] ?? [];
                        /** @var EditImage $editImage */
                        $editImage = app(EditImage::class);
                        $editImage->execute($data, $record, $galleries);
                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Image $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (Image $record) => $record->delete())
                    ->visible(fn (Image $record) => Gate::allows('delete', $record))
                    ->after(fn (Image $record) => Storage::delete($record->image_url)),
            ])
            ->headerActions([
                Action::make('view')
                    ->tooltip('View Internal Gallery')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->modalWidth('7xl')
                    ->modalHeading('Gallery')
                    ->modalContent(function () use ($room) {
                        return view('livewire.image-galleries.swiper-gallery', ['images' => $room->galleries->flatMap(fn ($gallery) => $gallery->images)]);
                    })
                    ->modalSubmitAction(false)
                    ->visible(fn () => $this->roomId),

                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->modalWidth('6xl')
                    ->createAnother(false)
                    ->modalHeading('Create Image')
                    ->form([
                        ...array_filter(
                            HotelImagesForm::getFormComponents(),
                            fn ($component) => ! (
                                ($component instanceof Select && $component->getName() === 'galleries') ||
                                ($component instanceof Select && $component->getName() === 'source') ||
                                ($component instanceof FileUpload && $component->getName() === 'image_url')
                            )
                        ),
                        FileUpload::make('image_url')
                            ->label('Images')
                            ->image()
                            ->imageEditor()
                            ->preserveFilenames()
                            ->directory('images')
                            ->disk(config('filament.default_filesystem_disk', 'public'))
                            ->visibility('private')
                            ->downloadable()
                            ->nullable()
                            ->multiple(),
                    ])
                    ->action(function ($data, \Filament\Forms\Form $form) use ($product, $room, $galleryName, $description) {
                        $galleries = $form->getRawState()['galleries'] ?? [];
                        /** @var AddImage $addImage */
                        $addImage = app(AddImage::class);
                        $addImage->addImagesToGallery($data, $product, $room, $galleryName, $description, $galleries);

                    })
                    ->visible(fn () => Gate::allows('create', Image::class)),

                Tables\Actions\Action::make('toggleViewMode')
                    ->label('Toggle View Mode')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(fn () => $this->toggleViewMode())
                    ->icon($this->viewMode === 'list' ? 'heroicon-o-table-cells' : 'heroicon-o-cube-transparent')
                    ->iconButton(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Source')
                    ->options(
                        Image::distinct()
                            ->whereNotNull('source')
                            ->pluck('source', 'source')
                            ->toArray()
                    ),
                SelectFilter::make('section_id')
                    ->label('Section')
                    ->options(ImageSection::pluck('name', 'id')->toArray()),
                SelectFilter::make('galleries')
                    ->label('Has Gallery')
                    ->options([
                        '1' => 'With Gallery',
                        '0' => 'Without Gallery',
                    ])
                    ->query(function (Builder $query, $data) {
                        if ((string) $data['value'] === '1') {
                            $query->whereHas('galleries');
                        } elseif ((string) $data['value'] === '0') {
                            $query->whereDoesntHave('galleries');
                        }
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotel-images.hotel-images-table');
    }

    protected function getListViewColumns(): array
    {
        return [
            TextColumn::make('id')
                ->sortable()
                ->searchable(),
            ImageColumn::make('image_url')
                ->label('Image url')
                ->size('100px'),
            TextColumn::make('tag')
                ->label('Tags')
                ->formatStateUsing(function ($state) {
                    if (! $state) {
                        return '';
                    }
                    $tags = explode(';', $state);

                    return collect($tags)
                        ->filter()
                        ->map(fn ($tag) => '<span class="inline-block bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded mr-1">'.e($tag).'</span>')
                        ->implode(' ');
                })
                ->html()
                ->wrap()
                ->searchable(),
            TextColumn::make('alt')
                ->searchable(),
            TextColumn::make('source')
                ->formatStateUsing(fn ($state) => ImageSourceEnum::tryFrom($state)?->label() ?? $state)
                ->searchable(),

            TextColumn::make('section.name')
                ->searchable(),
            TextColumn::make('galleries.gallery_name')
                ->searchable()
                ->badge()
                ->color('gray')
                ->url(fn ($record) => $record->galleries->first()?->id
                    ? route('image-galleries.edit', ['image_gallery' => $record->galleries->first()->id])
                    : null)
                ->openUrlInNewTab(),
        ];
    }

    protected function getGridViewColumns(): array
    {
        return [
            Tables\Columns\Layout\Grid::make()
                ->columns(1)
                ->schema([
                    ImageColumn::make('image_url')
                        ->size($this->viewMode === 'list' ? '200px' : '100%'),
                    TextColumn::make('source')
                        ->formatStateUsing(fn ($state) => ImageSourceEnum::tryFrom($state)?->label() ?? $state)
                        ->searchable(),
                    TextColumn::make('tag')
                        ->label('Tags')
                        ->formatStateUsing(function ($state) {
                            if (! $state) {
                                return '';
                            }
                            $tags = explode(';', $state);

                            return collect($tags)
                                ->filter()
                                ->map(fn ($tag) => '<span class="inline-block bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded mr-1">'.e($tag).'</span>')
                                ->implode(' ');
                        })
                        ->html()
                        ->wrap()
                        ->searchable(),
                    TextColumn::make('alt')
                        ->searchable(),
                    //                    TextColumn::make('galleries.gallery_name')
                    //                        ->searchable()
                    //                        ->wrap(),
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
