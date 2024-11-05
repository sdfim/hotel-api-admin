<?php

namespace Modules\HotelContentRepository\Livewire\HotelImages;

use App\Helpers\ClassHelper;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Storage;
use Modules\HotelContentRepository\Models\HotelImage;
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

class HotelImagesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([25, 50, 100])
            ->query(HotelImage::with(['section', 'galleries']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image_url')
                    ->size('100px'),
                TextColumn::make('tag')
                    ->searchable(isIndividual: true),
                TextColumn::make('section.name')
                    ->searchable(isIndividual: true),
                TextColumn::make('galleries.gallery_name')
                    ->searchable(isIndividual: true)
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                EditAction::make('edit')
                    ->iconButton()
                    ->url(fn (HotelImage $record): string => route('images.edit', $record))
                    ->visible(fn (HotelImage $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (HotelImage $record) => $record->delete())
                    ->visible(fn (HotelImage $record) => Gate::allows('delete', $record))
                    ->after(fn (HotelImage $record) => Storage::disk('public')->delete($record->image_url)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('images.create'))
                    ->visible(fn () => Gate::allows('create', HotelImage::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotel-images.hotel-images-table');
    }
}
