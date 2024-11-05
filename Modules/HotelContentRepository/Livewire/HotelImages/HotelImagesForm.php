<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Modules\HotelContentRepository\Models\HotelImage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Models\HotelImageSection;

class HotelImagesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public HotelImage $record;

    public function mount(HotelImage $hotelImage): void
    {
        $this->record = $hotelImage;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tag')
                    ->required()
                    ->maxLength(191),
                Select::make('section_id')
                    ->required()
                    ->options(HotelImageSection::pluck('name', 'id')),
                Select::make('galleries')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship('galleries', 'gallery_name'),
                TextInput::make('weight')
                    ->formatStateUsing(fn (HotelImage $record) => $this->record->exists ? $record->weight : '500'),
                FileUpload::make('image_url')
                    ->image()
                    ->imageEditor()
                    ->preserveFilenames()
                    ->directory('images')
                    ->disk('public')
                    ->visibility('public'),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        $galleries = $this->form->getRawState()['galleries'] ?? [];
        $this->record->galleries()->sync($galleries);

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
