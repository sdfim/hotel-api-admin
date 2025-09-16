<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Enums\ImageSourceEnum;
use Modules\HotelContentRepository\Actions\Gallery\AddGallery;
use Modules\HotelContentRepository\Actions\Gallery\DeleteGallery;
use Modules\HotelContentRepository\Actions\Gallery\EditGallery;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesForm;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\ImageSection;

class ImageGalleriesForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];

    public ImageGallery $record;

    public array $imageIds = [];

    public string $viewMode = 'table';

    public function mount(ImageGallery $imageGallery): void
    {
        $this->record = $imageGallery;
        $this->form->fill($this->record->attributesToArray());
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'table' : 'grid';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getGalleryFormComponents())
            ->statePath('data')
            ->model($this->record);
    }

    public static function getGalleryFormComponents(): array
    {
        return [
            TextInput::make('gallery_name')
                ->required()
                ->maxLength(191),
            TextInput::make('description')
                ->required()
                ->maxLength(191),
        ];
    }

    public function table(Table $table): Table
    {
        if ($this->viewMode != 'table') {
            $table = $table->contentGrid(['md' => 3, 'xl' => 4, '2xl' => 5]);
        }

        return $table
            ->heading('Images')
            ->paginated([10, 25, 50, 100])
            ->query(Image::query())
            ->modifyQueryUsing(function ($query) {
                match (true) {
                    $this->record->exists => $query->whereHas('galleries', fn ($query) => $query->where('gallery_id', $this->record->id)),
                    ! $this->record->exists => $query->whereIn('id', $this->imageIds),
                };
            })
            ->defaultSort('created_at', 'desc')
            ->columns($this->viewMode === 'grid' ? $this->getGridColumns() : $this->getTableColumns()) // Modify this line
            ->bulkActions([
                DeleteBulkAction::make('delete')
                    ->action(function ($records) {
                        /** @var DeleteGallery $deleteGallery */
                        $deleteGallery = app(DeleteGallery::class);
                        $deleteGallery->detachImages($records, $this->record, $this->imageIds);
                    }),
            ])
            ->headerActions([
                Action::make('view')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->modalWidth('7xl')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->modalHeading('Gallery')
                    ->modalContent(function () {
                        return view('livewire.image-galleries.swiper-gallery', ['images' => $this->record->images]);
                    })
                    ->modalSubmitAction(false),
                Action::make('toggleViewMode')
                    ->label('')
                    ->tooltip('Switch to '.($this->viewMode === 'grid' ? 'Table' : 'Grid').' View')
                    ->icon($this->viewMode === 'grid' ? 'heroicon-o-table-cells' : 'heroicon-o-cube-transparent')
                    ->iconButton()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(fn () => $this->toggleViewMode()),
                Action::make('Add Image')
                    ->tooltip('Add Image to Gallery')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->modalWidth('6xl')
                    ->iconButton()
                    ->form(array_filter(
                        HotelImagesForm::getFormComponents(),
                        fn ($component) => ! ($component instanceof \Filament\Forms\Components\Select && $component->getName() === 'galleries')
                    ))
                    ->action(function ($data) {
                        /** @var AddGallery $addGallery */
                        $addGallery = app(AddGallery::class);
                        $addGallery->execute($data, $this->record, $this->imageIds);
                    }),
                Action::make('Multiple Add Images To Gallery')
                    ->tooltip('Add Multiple Images To Gallery')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-squares-plus')
                    ->modalWidth('6xl')
                    ->iconButton()
                    ->form([
                        ...array_filter(
                            HotelImagesForm::getFormComponents(),
                            fn ($component) => ! (
                                ($component instanceof \Filament\Forms\Components\Select && $component->getName() === 'galleries') ||
                                ($component instanceof \Filament\Forms\Components\Select && $component->getName() === 'source') ||
                                ($component instanceof \Filament\Forms\Components\FileUpload && $component->getName() === 'image_url')
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
                    ->action(function ($data) {
                        /** @var AddGallery $addGallery */
                        $addGallery = app(AddGallery::class);
                        $addGallery->executeMultiple($data, $this->record, $this->imageIds);
                    }),
            ])
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->modalWidth('6xl')
                    ->form(HotelImagesForm::getFormComponents('', '', true)),
                DeleteAction::make()
                    ->iconButton()
                    ->action(function (Image $record) {
                        /** @var DeleteGallery $deleteGallery */
                        $deleteGallery = app(DeleteGallery::class);
                        $deleteGallery->detachImage($record, $this->record, $this->imageIds);
                    }),
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
                    ->options(
                        ImageSection::whereNotNull('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    ),
            ]);
    }

    private function getGridColumns(): array // Add this method
    {
        return [
            Tables\Columns\Layout\Grid::make()
                ->columns(1)
                ->schema([
                    ImageColumn::make('image_url')
                        ->size('200px')
                        ->getStateUsing(fn ($record) => $record->full_url),
                    TextColumn::make('tag')
                        ->searchable(),
                ]),
        ];
    }

    private function getTableColumns(): array // Add this method
    {
        return [
            TextColumn::make('id')
                ->sortable()
                ->searchable(),
            ImageColumn::make('image_preview')
                ->label('Image')
                ->size('100px')
                ->getStateUsing(fn ($record) => $record->full_url),
            TextColumn::make('image_url')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('weight')
                ->searchable()
                ->sortable(
                    query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("CAST(weight AS UNSIGNED) {$direction}");
                    }
                )
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('tag')
                ->searchable(),
            TextColumn::make('section.name')
                ->searchable(),
            TextColumn::make('source')
                ->formatStateUsing(fn ($state) => ImageSourceEnum::tryFrom($state)?->label() ?? $state)
                ->searchable(),
        ];
    }

    private function getSelectImages(?string $search = null): Collection
    {
        return Image::with('section')
            ->when($search, fn ($query) => $query->where('tag', 'LIKE', "%{$search}%"))
            ->whereDoesntHave(
                'galleries',
                fn ($query) => $query->where('gallery_id', $this->record->id),
            )
            ->whereNotIn('id', $this->imageIds)
            ->get();
    }

    private function prepareSelectImages(Collection $images): \Illuminate\Support\Collection
    {
        return $images->mapWithKeys(function ($image) {
            if (str_contains($image->image_url, 'http')) {
                $url = $image->image_url;
            } else {
                $url = Storage::url($image->image_url);
            }

            return [
                $image->id => '<div class="flex flex-row gap-3">'.
                    '<img src="'.$url.'" alt="img" style="max-width: 100px;max-height: 100px;">'.
                    '<span>'.$image->tag.',</span>'.
                    '<span>'.$image->section?->name.'</span>'.
                    '</div>',
            ];
        });
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        /** @var EditGallery $editGallery */
        $editGallery = app(EditGallery::class);
        $editGallery->execute($data, $this->record, $this->imageIds);
        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('image-galleries.index');
    }

    public function render(): View
    {
        return view('livewire.image-galleries.image-galleries-form');
    }
}
