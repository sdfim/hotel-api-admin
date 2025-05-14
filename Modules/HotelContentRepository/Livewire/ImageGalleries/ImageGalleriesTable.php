<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
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
                        HotelImagesForm::getFormComponents($filePath),
                        fn ($component) => ! ($this->productId && $component instanceof Select && $component->getName() === 'galleries')
                    ))
                    ->action(function ($data) use ($product, $galleryName, $description) {
                        /** @var AddGallery $addGallery */
                        $addGallery = app(AddGallery::class);
                        $addGallery->addImageToGallery($data, $product, $galleryName, $description);
                    })
                    ->visible(fn () => $this->productId && Gate::allows('create', ImageGallery::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.image-galleries.image-galleries-table');
    }
}
