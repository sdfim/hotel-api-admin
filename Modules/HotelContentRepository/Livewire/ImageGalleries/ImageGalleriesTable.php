<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesTable;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesForm;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ImageGalleriesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $productId = null;

    public function  mount(?int $productId): void
    {
        $this->productId = $productId;
    }

    public function table(Table $table): Table
    {
        $filePath = $galleryName = $description = '';
        $product = $room = null;
        if ($this->productId) {
            $product = \Modules\HotelContentRepository\Models\Product::find($this->productId);
            ['filePath' => $filePath, 'galleryName' => $galleryName, 'description' => $description] = HotelImagesTable::generateGalleryDetails($product);
            $description = 'Product Image Gallery: ' . $galleryName;
        }

        return $table
            ->paginated([25, 50, 100])
            ->query(function () {
                $query = ImageGallery::query();
                if ($this->productId) {
                    $query->hasProduct($this->productId);
                }
                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ImageGallery $record): string|null =>
                Gate::allows('update', $record) ? route('image-galleries.edit', $record) : null
            )
            ->columns([
                ImageColumn::make('images.image_url')
                    ->size('70px')
                    ->stacked()
                    ->circular()
                    ->extraAttributes(['class' => 'rounded-full'])
                    ->getStateUsing(function ($record) {
                        return collect($record->images)
                            ->shuffle()
                            ->pluck('image_url')
                            ->toArray();
                    })
                    ->limit(4)
                    ->limitedRemainingText(),
                TextColumn::make('gallery_name')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('description')->wrap(),
            ])
            ->actions([
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
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form(ImageGalleriesForm::getGalleryFormComponents())
                    ->visible(fn () => !$this->productId && Gate::allows('create', ImageGallery::class)),
                Action::make('Add Image')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->tooltip('Add Image to Product Gallery')
                    ->modalHeading('Create Image')
                    ->form(array_filter(
                        HotelImagesForm::getFormComponents($filePath),
                        fn($component) => !($this->productId && $component instanceof Select && $component->getName() === 'galleries')
                    ))
                    ->action(function ($data) use ($product, $room, $galleryName, $description) {
                        DB::transaction(function () use ($data, $room, $product, $galleryName, $description) {
                            $image = Image::create([
                                'image_url'  => $data['image_url'],
                                'tag'        => $data['tag'],
                                'alt'        => $data['alt'],
                                'section_id' => $data['section_id'],
                                'weight'     => $data['weight'] ?? '500px',
                            ]);

                            if ($this->productId) {
                                $gallery = ImageGallery::firstOrCreate(
                                    ['gallery_name' => $galleryName],
                                    ['description' => $description]
                                );
                                $gallery->images()->attach($image->id);
                                $product->galleries()->syncWithoutDetaching([$gallery->id]);
                            }
                        });
                    })
                    ->visible(fn () => $this->productId && Gate::allows('create', ImageGallery::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.image-galleries.image-galleries-table');
    }
}
