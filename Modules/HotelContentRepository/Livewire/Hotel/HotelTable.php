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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Filament\CustomTextColumn;
use Modules\HotelContentRepository\Models\Hotel;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Hotel::with([
                'affiliations',
                'attributes',
                'contentSource',
                'roomImagesSource',
                'propertyImagesSource',
                'descriptiveContentsSection',
                'feeTaxes',
                'informativeServices',
                'promotions',
                'rooms',
                'keyMappings',
                'galleries',
                'contactInformation'
            ]))
            ->columns([
                BooleanColumn::make('verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->wrap(),
//                CustomTextColumn::make('type')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('weight')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('address')
                    ->searchable(isIndividual: true)
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
                CustomTextColumn::make('star_rating')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
//                CustomTextColumn::make('website')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('num_rooms')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
//                CustomTextColumn::make('location')
//                    ->searchable(isIndividual: true)
//                    ->getStateUsing(function ($record) {
//                        $string = '';
//                        foreach ($record->location as $key => $item)    {
//                            if (is_array($item)) continue;
//                            $string .= $key .  ': ' . $item . ', ';
//                        }
//                        return $string;
//                    })
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return $record->contentSource->name . ' ' . $record->roomImagesSource->name . ' ' . $record->propertyImagesSource->name;
                    }),
//                TextColumn::make('galleries')
//                    ->label('Galleries')
//                    ->searchable(isIndividual: true)
//                    ->formatStateUsing(function ($state) {
//                        $items = explode(', ', $state);
//                        $string = '';
//                        foreach ($items as $item) {
//                            $dataItem = json_decode($item, true);
//                            if (is_null($dataItem)) {
//                                continue;
//                            }
//                            $string .= $dataItem['gallery_name'] . '</b><br>';
//                        }
//                        return $string;
//                    })
//                    ->html()
//                    ->wrap()
//                    ->toggleable()
//                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel_repository.edit', $record))
                    ->visible(fn (Hotel $record) => Gate::allows('update', $record))
                ,
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record)),
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
            'type',
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
            'channel_management',
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

        return redirect()->route('hotel_repository.index');
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
