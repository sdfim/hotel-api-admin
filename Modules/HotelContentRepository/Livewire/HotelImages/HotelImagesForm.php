<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Actions\Image\AddImage;
use Modules\HotelContentRepository\Actions\Image\EditImage;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesForm;
use Modules\HotelContentRepository\Models\Image;
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

    public static function getFormComponents(?string $filePath = ''): array
    {
        return [
            TextInput::make('tag')
                ->label('Tag')
                ->required()
                ->maxLength(191),
            Select::make('section_id')
                ->label('Section')
                ->required()
                ->options(ImageSection::pluck('name', 'id')),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->searchable()
                ->native(false)
                ->relationship('galleries', 'gallery_name')
                ->createOptionForm(ImageGalleriesForm::getGalleryFormComponents())
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
            TextInput::make('weight')
                ->label('Weight')
                ->formatStateUsing(fn (?Image $record) => $record?->exists ? $record->weight : '500'),
            TextInput::make('alt')
                ->label('Alt')
                ->maxLength(191),
            FileUpload::make('image_url')
                ->label('Image')
                ->image()
                ->imageEditor()
                ->preserveFilenames()
                ->directory($filePath ? 'images/'.$filePath : 'images')
                ->disk('public')
                ->visibility('public'),
        ];
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
