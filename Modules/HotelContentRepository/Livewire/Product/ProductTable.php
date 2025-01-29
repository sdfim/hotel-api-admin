<?php

namespace Modules\HotelContentRepository\Livewire\Product;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Vendor;

class ProductTable extends Component implements HasForms, HasTable
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
            ->query(function () {
                $query = Product::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id)
                    );

                if ($this->vendor?->id) {
                    $query->where('vendor_id', $this->vendor->id);
                }

                return $query;
            })
            ->columns([
                ImageColumn::make('hero_image_thumbnails')
                    ->size('100px'),

                IconColumn::make('verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('product_type')
                    ->label('Type')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('related.address')
                    ->label('Address')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('default_currency')
                    ->label('Currency')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return 'Content: '.$record->contentSource->name.'<br>'
                            .'Room Images: '.$record->related?->roomImagesSource->name.'<br>'
                            .'Property Images: '.$record->propertyImagesSource->name;
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
                    ->url(function ($record): string {
                        //                        dump($record->product_type);
                        return $record->product_type === 'hotel'
                            ? route('hotel-repository.edit', $record->related)
                            : route('product-repository.edit', $record);
                    })
                    ->visible(fn (Product $record) => Gate::allows('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->action(function (Product $record) {
                        /** @var DeleteProduct $deleteProduct */
                        $deleteProduct = app(DeleteProduct::class);
                        $deleteProduct->deleteWithRelated($record);
                        Notification::make()
                            ->title('Product deleted successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Product $record): bool => Gate::allows('delete', $record)),
            ]);
    }

    private function create($data): Redirector|RedirectResponse
    {
        $data['address'] = array_reduce($data['address'], function ($result, $item) {
            $result[$item['field']] = $item['value'];

            return $result;
        }, []);

        if (! isset($data['verified'])) {
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

        $product = Product::create(Arr::only($data, [
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
            'default_currency',
        ]));

        if (isset($data['galleries'])) {
            $product->galleries()->sync($data['galleries']);
        }

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('product.index');
    }

    public function render(): View
    {
        return view('livewire.products.product-table');
    }
}
