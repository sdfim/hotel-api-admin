<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    private ?Vendor $vendor;

    public function mount(Hotel $hotel, ?Vendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function () {
                $query = Hotel::query();
                if ($this->vendor->exists) {
                    $query->whereHas('product', function (Builder $query) {
                        $query->where('vendor_id', $this->vendor->id);
                    });
                }
                return $query;
            })
            ->columns([
                BooleanColumn::make('product.verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('product.name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('product.address')
                    ->label('Address')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $string = '';
                        foreach ($record->address as $item) {
                            if (is_array($item)) continue;
                            $string .= $item . ', ';
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
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return $record->product->contentSource->name . ' '
                            . $record->roomImagesSource->name . ' '
                            . $record->product->propertyImagesSource->name;
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel-repository.edit', $record))
                    ->visible(fn (Hotel $record) => Gate::allows('update', $record))
                ,
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record))
                    ->action(function (Hotel $record) {
                        \DB::transaction(function () use ($record) {
                            $record->product->delete();
                            $record->delete();
                        });
                        Notification::make()
                            ->title('Hotel deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form((new HotelForm())->schemeForm())
                    ->visible(Gate::allows('create', Hotel::class))
                    ->tooltip('Add New Hotel')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function ($data) {
                        return $this->create($data);
                    }),
            ]);
    }

    private function create($data): Redirector|RedirectResponse
    {
        $data['address'] = array_reduce($data['address'], function ($result, $item) {
            $result[$item['field']] = $item['value'];
            return $result;
        }, []);

        if (!isset($data['verified'])) {
            $data['verified'] = false;
        }

        if (isset($data['location'])) {
            $data['location'] = array_reduce($data['location'], function ($result, $item) {
                $result[$item['field']] = $item['value'];
                return $result;
            }, []);
        } else {
            $data['location'] = [
                'latitude' => $data['lat'] ?? 0,
                'longitude' => $data['lng'] ?? 0,
            ];
        }

        $hotel = Hotel::create(Arr::only($data, [
            'name',
            'location',
            'sale_type',
            'verified',
            'lat',
            'lng',
            'address',
            'star_rating',
            'website',
            'num_rooms',
            'featured',
            'content_source_id',
            'room_images_source_id',
            'property_images_source_id',
            'travel_agent_commission',
            'hotel_board_basis',
            'default_currency'
        ]));

        if (isset($data['galleries'])) {
            $hotel->galleries()->sync($data['galleries']);
        }

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('hotel-repository.index');
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
