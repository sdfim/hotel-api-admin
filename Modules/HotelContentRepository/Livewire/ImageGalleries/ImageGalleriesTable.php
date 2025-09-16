<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\MultiSelectFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\Gallery\AddGallery;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesForm;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesTable;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;

class ImageGalleriesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $productId = null;

    public function mount(?Product $product = null): void
    {
        $this->productId = $product->id;
    }

    public function table(Table $table): Table
    {
        $filePath = $galleryName = $description = '';
        $product = $room = null;
        if ($this->productId) {
            $product = Product::find($this->productId);
            ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = HotelImagesTable::generateGalleryDetails($product);
            $description = 'Product Image Gallery: '.$galleryName;
        }

        return $table
            ->paginated([10, 25, 50, 100])
            ->query(function () {
                $query = ImageGallery::query();
                if ($this->productId) {
                    $query->hasProduct($this->productId);
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ImageGallery $record): ?string => Gate::allows('update', $record) ? route('image-galleries.edit', $record) : null
            )
            ->deferLoading()
            ->columns([
                ImageColumn::make('images.image_url')
                    ->size('70px')
                    ->stacked()
                    ->circular()
                    ->extraAttributes(['class' => 'rounded-full'])
                    ->getStateUsing(function ($record) {
                        return collect($record->images)
                            ->shuffle()
                            ->map(fn ($image) => $image['full_url'])
                            ->toArray();
                    })
                    ->limit(4)
                    ->limitedRemainingText(),
                TextColumn::make('gallery_name')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('description')->wrap(),
                TextColumn::make('attached_models')
                    ->label('Attached Models')
                    ->getStateUsing(function ($record) {
                        if (! $record) {
                            return [];
                        }
                        $badges = collect();
                        $types = [
                            'Vendor' => ['relation' => 'vendors', 'color' => 'bg-yellow-700'],
                            'Room' => ['relation' => 'hotelRooms', 'color' => 'bg-green-500'],
                            'Product' => ['relation' => 'products', 'color' => 'bg-purple-500'],
                        ];
                        foreach ($types as $type => $info) {
                            $items = $record->{$info['relation']} ?? collect();
                            $count = $items instanceof \Illuminate\Support\Collection ? $items->count() : (is_array($items) ? count($items) : 0);
                            if ($count > 0) {
                                $badges->push('<span class="px-2 py-1 rounded text-white '.$info['color'].'">'.$type.' ('.$count.')</span>');
                            }
                        }

                        return $badges->join('<br>');
                    })
                    ->html()
                    ->wrap(),
            ])
            ->actions([
                Action::make('view')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->modalWidth('7xl')
                    ->modalHeading('Gallery')
                    ->modalContent(function (ImageGallery $record) {
                        return view('livewire.image-galleries.swiper-gallery', ['images' => $record->images]);
                    })
                    ->modalSubmitAction(false),
                EditAction::make('edit')
                    ->iconButton()
                    ->form(ImageGalleriesForm::getGalleryFormComponents())
                    ->fillForm(fn (ImageGallery $record) => $record->attributesToArray())
                    ->visible(fn (ImageGallery $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (ImageGallery $record) => $record->delete())
                    ->visible(fn (ImageGallery $record) => Gate::allows('delete', $record)),
                Action::make('manage_attachments')
                    ->iconButton()
                    ->icon('heroicon-o-cog')
                    ->modalWidth('4xl')
                    ->modalHeading(fn (ImageGallery $record) => 'Manage Attachments for Gallery: '.($record->gallery_name ?? ''))
                    ->modalContent(function (ImageGallery $record) {
                        // Modal content will be generated by Filament form below
                        return null;
                    })
                    ->form([
                        CustomRepeater::make('attachments')
                            ->label('Attach Models')
                            ->schema([
                                Select::make('model_type')
                                    ->label('')
                                    ->options([
                                        'Vendor' => 'Vendor',
                                        'Room' => 'Room',
                                        'Product' => 'Product',
                                    ])
                                    ->reactive()
                                    ->required(),
                                Select::make('model_id')
                                    ->label('')
                                    ->multiple()
                                    ->options(function (callable $get) {
                                        $type = $get('model_type');
                                        if ($type === 'Vendor') {
                                            return \Modules\HotelContentRepository\Models\Vendor::pluck('name', 'id');
                                        }
                                        if ($type === 'Room') {
                                            $selectedIds = (array) $get('model_id');
                                            $roomsQuery = \Modules\HotelContentRepository\Models\HotelRoom::with('hotel');
                                            $rooms = $roomsQuery->limit(20)->get();
                                            if (! empty($selectedIds)) {
                                                $selectedRooms = $roomsQuery->whereIn('id', $selectedIds)->get();
                                                $rooms = $rooms->concat($selectedRooms)->unique('id');
                                            }

                                            return $rooms->mapWithKeys(function ($room) {
                                                $roomName = $room->name ?: 'Room #'.$room->id;
                                                $hotelName = $room->hotel && $room->hotel->name ? $room->hotel->name : '';
                                                $label = $hotelName ? ($roomName.' ('.$hotelName.')') : $roomName;

                                                return [$room->id => $label];
                                            });
                                        }
                                        if ($type === 'Product') {
                                            return \Modules\HotelContentRepository\Models\Product::pluck('name', 'id');
                                        }

                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->default(function (ImageGallery $record) {
                                $data = [];
                                $promotionIds = ($record->productPromotions ?? collect())->pluck('id')->all();
                                if ($promotionIds) {
                                    $data[] = ['model_type' => 'Promotion', 'model_id' => $promotionIds];
                                }
                                $vendorIds = ($record->vendors ?? collect())->pluck('id')->all();
                                if ($vendorIds) {
                                    $data[] = ['model_type' => 'Vendor', 'model_id' => $vendorIds];
                                }
                                $roomIds = ($record->hotelRooms ?? collect())->pluck('id')->all();
                                if ($roomIds) {
                                    $data[] = ['model_type' => 'Room', 'model_id' => $roomIds];
                                }
                                $productIds = ($record->products ?? collect())->pluck('id')->all();
                                if ($productIds) {
                                    $data[] = ['model_type' => 'Product', 'model_id' => $productIds];
                                }

                                return $data;
                            }),
                    ])
                    ->action(function ($data, ImageGallery $record) {
                        // Detach all current attachments
                        $record->vendors()->detach();
                        $record->hotelRooms()->detach();
                        $record->products()->detach();
                        // Attach new ones
                        foreach ($data['attachments'] as $attachment) {
                            $ids = is_array($attachment['model_id']) ? $attachment['model_id'] : [$attachment['model_id']];
                            switch ($attachment['model_type']) {
                                case 'Vendor':
                                    $record->vendors()->attach($ids);
                                    break;
                                case 'Room':
                                    $record->hotelRooms()->attach($ids);
                                    break;
                                case 'Product':
                                    $record->products()->attach($ids);
                                    break;
                            }
                        }
                    })
                    ->visible(fn (ImageGallery $record) => Gate::allows('update', $record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form(ImageGalleriesForm::getGalleryFormComponents())
                    ->visible(fn () => ! $this->productId && Gate::allows('create', ImageGallery::class)),
                Action::make('Add Image')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->modalWidth('6xl')
                    ->tooltip('Add Image to Product Gallery')
                    ->modalHeading('Create Image')
                    ->form(array_filter(
                        HotelImagesForm::getFormComponents($filePath, $this->productId ? 'hotel' : ''),
                        fn ($component) => ! ($this->productId && $component instanceof Select && $component->getName() === 'galleries')
                    ))
                    ->action(function ($data) use ($product, $galleryName, $description) {
                        /** @var AddGallery $addGallery */
                        $addGallery = app(AddGallery::class);
                        $addGallery->addImageToGallery($data, $product, $galleryName, $description);
                    })
                    ->visible(fn () => $this->productId && Gate::allows('create', ImageGallery::class)),
            ])
            ->filters([
                MultiSelectFilter::make('vendors')
                    ->label('Vendors')
                    ->relationship('vendors', 'name'),
                MultiSelectFilter::make('hotelRooms')
                    ->label('Rooms')
                    ->relationship('hotelRooms', 'name'),
                MultiSelectFilter::make('products')
                    ->label('Products')
                    ->relationship('products', 'name'),
                SelectFilter::make('model_type')
                    ->label('Model Type')
                    ->options([
                        'vendors' => 'Vendor',
                        'hotelRooms' => 'Room',
                        'products' => 'Product',
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas($data['value']);
                        }

                        return $query;
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.image-galleries.image-galleries-table');
    }
}
