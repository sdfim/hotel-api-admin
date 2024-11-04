<?php

namespace Modules\HotelContentRepository\Livewire\ImageGalleries;

use App\Helpers\ClassHelper;
use Filament\Tables\Columns\ImageColumn;
use Modules\HotelContentRepository\Models\ImageGallery;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
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

    public function table(Table $table): Table
    {
        return $table
            ->paginated([25, 50, 100])
            ->query(ImageGallery::query())
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ImageGallery $record): string|null =>
                Gate::allows('update', $record) ? route('image-galleries.edit', $record) : null
            )
            ->columns([
                ImageColumn::make('images.image_url')
                    ->size('100px')
                    ->stacked()
                    ->limit()
                    ->limitedRemainingText(),
                TextColumn::make('gallery_name')
                    ->searchable(),
                TextColumn::make('description'),
            ])
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->url(fn (ImageGallery $record): string => route('image-galleries.edit', $record))
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
                    ->url(fn (): string => route('image-galleries.create'))
                    ->visible(fn () => Gate::allows('create', ImageGallery::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.image-galleries.image-galleries-table');
    }
}
