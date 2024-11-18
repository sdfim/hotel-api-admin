<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\ImageGallery;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ImageGalleriesForm extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?array $data = [];
    public ImageGallery $record;
    public array $imageIds = [];
    public string $viewMode = 'grid';

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
            ->schema([
                TextInput::make('gallery_name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Images')
            ->paginated([25, 50, 100])
            ->query(
                Image::query()
                    ->when($this->record->exists, function ($query) {
                        return $query->whereHas('galleries', fn ($query) => $query->where('gallery_id', $this->record->id));
                    })
                    ->when(!$this->record->exists, function ($query) {
                        return $query->whereIn('id', $this->imageIds);
                    })
            )
            ->defaultSort('created_at', 'desc')
            ->columns($this->viewMode === 'grid' ? $this->getGridColumns() : $this->getTableColumns()) // Modify this line
            ->contentGrid(['md' => 3, 'xl' => 4, '2xl' => 5])
            ->bulkActions([
                DeleteBulkAction::make('delete')
                    ->action(function ($records) {
                        if ($this->record->exists) {
                            $this->record->images()->detach($records->pluck('id'));
                        } else {
                            $ids = $records->pluck('id')->all();
                            $this->imageIds = array_filter($this->imageIds, fn ($id) => !in_array($id, $ids));
                        }
                    }),
            ])
            ->headerActions([
                Action::make('toggleViewMode')
                    ->label('')
                    ->tooltip('Switch to ' . ($this->viewMode === 'grid' ? 'Table' : 'Grid') . ' View')
                    ->icon($this->viewMode === 'grid' ? 'heroicon-o-table-cells' : 'heroicon-o-cube-transparent')
                    ->iconButton()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(fn() => $this->toggleViewMode()),
                Action::make('Create image')
                    ->tooltip('Create image')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->form([
                        TextInput::make('tag')
                            ->required(),
                        Select::make('section_id')
                            ->required()
                            ->label('Section')
                            ->options(ImageSection::pluck('name', 'id')),
                        TextInput::make('weight'),
                        FileUpload::make('image')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->preserveFilenames()
                            ->directory('images')
                            ->disk('public'),
                    ])
                    ->action(function($data) {
                        $image = Image::create([
                            'image_url'  => $data['image'],
                            'tag'        => $data['tag'],
                            'section_id' => $data['section_id'],
                            'weight'     => $data['weight'] ?? '500px',
                        ]);

                        if ($this->record->exists) {
                            $this->record->images()->attach($image->id);
                        } else {
                            $this->imageIds[] = $image->id;
                        }
                    }),
                Action::make('Add existing image')
                    ->tooltip('Add existing image')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-link')
                    ->iconButton()
                    ->form([
                        Select::make('image_ids')
                            ->label('Images')
                            ->required()
                            ->allowHtml()
                            ->searchable()
                            ->multiple()
                            ->options(
                                $this->prepareSelectImages($this->getSelectImages())
                            )
                            ->getSearchResultsUsing(function ($search) {
                                return $this->prepareSelectImages($this->getSelectImages($search));
                            })
                    ])->action(function ($data) {
                        if ($this->record->exists) {
                            $this->record->images()->attach($data['image_ids']);
                        } else {
                            $this->imageIds = [...$this->imageIds, ...$data['image_ids']];
                        }
                    }),
            ])
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->form([
                        TextInput::make('tag'),
                        Select::make('section_id')
                            ->options(ImageSection::pluck('name', 'id')),
                        FileUpload::make('image_url')
                            ->image()
                            ->imageEditor()
                            ->preserveFilenames()
                            ->directory('images')
                            ->disk('public')
                            ->visibility('public'),
                    ]),
                DeleteAction::make()
                    ->iconButton()
                    ->action(function (Image $record) {
                        if ($this->record->exists) {
                            $this->record->images()->detach($record->id);
                        } else {
                            $this->imageIds = array_filter($this->imageIds, fn ($id) => $id != $record->id);
                        }
                    }),
            ]);
    }

    private function getGridColumns(): array // Add this method
    {
        return [
            Tables\Columns\Layout\Grid::make()
                ->columns(1)
                ->schema([
                    ImageColumn::make('image_url')
                        ->size('200px'),
                    TextColumn::make('tag')
                        ->searchable(),
                    TextColumn::make('section.name')
                        ->searchable(),
                    TextColumn::make('weight')
                        ->searchable(),
                ])
        ];
    }

    private function getTableColumns(): array // Add this method
    {
        return [
            TextColumn::make('id')
                ->sortable()
                ->searchable(),
            ImageColumn::make('image_url')
                ->size('100px'),
            TextColumn::make('tag')
                ->searchable(),
            TextColumn::make('section.name')
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
                $url = Storage::disk('public')->url($image->image_url);
            }

            return [
                $image->id => '<div class="flex flex-row gap-3">' .
                    '<img src="' . $url . '" alt="img" style="max-width: 100px;max-height: 100px;">' .
                    '<span>' . $image->tag . ',</span>' .
                    '<span>' . $image->section?->name . '</span>' .
                    '</div>'
            ];
        });
    }

    public function edit(): Redirector|RedirectResponse
    {
        $exists = $this->record->exists;
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        if (!$exists) {
            $this->record->images()->attach($this->imageIds);
        }

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
