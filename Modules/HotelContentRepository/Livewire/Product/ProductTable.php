<?php

namespace Modules\HotelContentRepository\Livewire\Product;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;

class ProductTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Product::query())
            ->columns([
                BooleanColumn::make('verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),

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

                TextColumn::make('location')
                    ->toggleable()
                    ->sortable(),

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
                        return $record->contentSource->name . ' '
                            . $record->related->roomImagesSource->name . ' '
                            . $record->propertyImagesSource->name;
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn ($record): string => $record->product_type === 'hotel'
                        ? route('hotel-repository.edit', $record->related)
                        : route('product-repository.edit', $record))
                    ->visible(fn (Product $record) => Gate::allows('update', $record))
                ,
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->action(function (Product $record) {
                        \DB::transaction(function () use ($record) {
                            $record->related->delete();
                            $record->delete();
                        });
                        Notification::make()
                            ->title('Product deleted successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Product $record): bool => Gate::allows('delete', $record)),
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
            'default_currency'
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
