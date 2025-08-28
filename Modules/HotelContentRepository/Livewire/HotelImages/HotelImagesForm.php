<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Enums\ImageSourceEnum;
use Modules\HotelContentRepository\Actions\Image\AddImage;
use Modules\HotelContentRepository\Actions\Image\EditImage;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesForm;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\ImageSection;

class HotelImagesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Image $record;

    public function mount(Image $repositoryImage): void
    {
        $this->record = $repositoryImage;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormComponents())
            ->statePath('data')
            ->model($this->record);
    }

    public static function getFormComponents(?string $filePath = '', string $sectionName = '', bool $recordExists = false): array
    {
        return [
            Grid::make()
                ->schema([
                    TextInput::make('tag')
                        ->label('Tag')
                        ->required()
                        ->maxLength(191),
                    Select::make('section_id')
                        ->label('Section')
                        ->required()
                        ->options(ImageSection::pluck('name', 'id'))
                        ->default(function () use ($sectionName) {
                            if (! empty($sectionName)) {
                                $section = ImageSection::where('name', $sectionName)->first();

                                return $section?->id;
                            }

                            return null;
                        })
                        ->disabled(! empty($sectionName) || $recordExists)
                        ->dehydrated(true), // Ensure the value is included in form data even when disabled
                ]),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->searchable()
                ->native(false)
                ->relationship('galleries', 'gallery_name')
                ->createOptionForm(Gate::allows('create', ImageGallery::class) ? ImageGalleriesForm::getGalleryFormComponents() : [])
                ->createOptionUsing(function (array $data) {
                    /** @var AddImage $addImage */
                    $addImage = app(AddImage::class);
                    $image = $addImage->createImage($data);
                    Notification::make()
                        ->title('Gallery created successfully')
                        ->success()
                        ->send();

                    return $image->id;
                }),

            Grid::make()
                ->schema([
                    TextInput::make('weight')
                        ->label('Weight')
                        ->formatStateUsing(fn (?Image $record) => $record?->exists ? $record->weight : '500'),
                    TextInput::make('alt')
                        ->label('Alt')
                        ->maxLength(191),
                    Select::make('source')
                        ->label('Source')
                        ->options(ImageSourceEnum::getOptions())
                        ->default(ImageSourceEnum::OWN->value)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('source', $state)),

                ]),

            FileUpload::make('image_url')
                ->label('Image')
                ->image()
                ->imageEditor()
                ->preserveFilenames()
                ->directory($filePath ? 'images/'.$filePath : 'images')
                ->disk(self::getDisk())
                ->visibility('private')
                ->downloadable()
                ->nullable()
                ->visible(fn ($get) => $get('source') === 'own'),

            Grid::make(3)
                ->schema([
                    TextInput::make('base_url')
                        ->label('Base URL')
                        ->formatStateUsing(fn () => config('image_sources.sources.crm'))
                        ->disabled()
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('source') === 'crm'),
                    TextInput::make('image_url_txt')
                        ->label('Image URL')
                        ->required()
                        ->columnSpan(fn ($get) => $get('source') === 'crm' ? 2 : 3)
                        ->formatStateUsing(fn ($record) => $record ? $record->image_url : '')
                        ->afterStateUpdated(fn ($state, callable $set) => $set('image_url', $state)),
                ])
                ->visible(fn ($get) => $get('source') !== 'own'),
        ];
    }

    private static function getDisk(): string
    {
        $disk = config('filament.default_filesystem_disk', 'public');
        Log::debug('HotelImagesForm FileUpload disk:', ['disk' => $disk]);

        return $disk;
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $galleries = $this->form->getRawState()['galleries'] ?? [];
        /** @var EditImage $editImage */
        $editImage = app(EditImage::class);
        $editImage->execute($data, $this->record, $galleries);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('images.index');
    }

    public function render(): View
    {
        return view('livewire.hotel-images.hotel-images-form');
    }
}
