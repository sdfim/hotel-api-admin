<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Actions\Hotel\DeleteHotel;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Vendor $vendor = null;

    public function mount(?Vendor $vendor = null)
    {
        $this->vendor = $vendor;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function (Builder $query) {
                $query = Hotel::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->whereHas('product', function (Builder $query) {
                            $query->where('vendor_id', auth()->user()->currentTeam->vendor_id);
                        })
                    );

                if ($this->vendor?->id) {
                    $query->whereHas('product', function (Builder $query) {
                        $query->where('vendor_id', $this->vendor->id);
                    });
                }

                return $query;
            })
            ->columns([
                ImageColumn::make('product.hero_image_thumbnails')
                    ->size('100px'),

                TextColumn::make('rooms.name')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('product.verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('product.name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('giata_code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('product.address')
                    ->label('Address')
                    ->searchable()
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $string = '';
                        foreach ($record->address as $item) {
                            if (is_array($item)) {
                                continue;
                            }
                            $string .= $item.', ';
                        }

                        return rtrim($string, ', ');
                    })
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('star_rating')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('num_rooms')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return 'Content: '.$record->product?->contentSource->name.'<br>'
                            .'Room Images: '.$record->roomImagesSource->name.'<br>'
                            .'Property Images: '.$record->product?->propertyImagesSource->name;
                    })
                    ->html(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date()
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel-repository.edit', $record))
                    ->visible(fn (Hotel $record) => Gate::allows('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record))
                    ->action(function (Hotel $record) {
                        \DB::transaction(function () use ($record) {
                            if ($record->product) {
                                /** @var DeleteProduct $DeleteProductAction */
                                $DeleteProductAction = app(DeleteProduct::class);
                                $DeleteProductAction->handle($record->product);
                            }
                            /** @var DeleteHotel $DeleteHotelAction */
                            $DeleteHotelAction = app(DeleteHotel::class);
                            $DeleteHotelAction->handle($record);
                        });
                        Notification::make()
                            ->title('Hotel deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('addHotelWithGiataCode')
                    ->label('Add Hotel with GIATA Code')
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->createAnother(false)
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(function (array $data) {
                        $this->saveHotelWithGiataCode($data);
                    })
                    ->modalHeading('Add Hotel with GIATA Code')
                    ->modalWidth('lg')
                    ->form(HotelForm::getCoreFields()),
            ]);
    }

    public function saveHotelWithGiataCode($data): void
    {
        try {
            /** @var AddHotel $hotelAction */
            $hotelAction = app(AddHotel::class);
            $hotel = $hotelAction->saveWithGiataCode($data);

            Notification::make()
                ->title('Hotel created successfully')
                ->success()
                ->send();

            $this->redirect(route('hotel-repository.edit', $hotel));
        } catch (\Exception $e) {
            Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();

            $this->redirect(route('hotel-repository.index'));
        }
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
