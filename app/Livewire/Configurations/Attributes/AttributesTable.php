<?php

namespace App\Livewire\Configurations\Attributes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ProductAttribute;

class AttributesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigAttribute::query()->withCount('products', 'hotelRooms'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->categories->pluck('name')->map(fn ($name) => \Illuminate\Support\Str::of($name)->replace('_', ' ')->title())->join(', ');
                    }),
                TextColumn::make('products_count')
                    ->label('Hotels')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('hotel_rooms_count')
                    ->label('Rooms')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('with_hotel_rooms')
                    ->label('With Rooms')
                    ->query(fn ($query) => $query->has('hotelRooms')),
                \Filament\Tables\Filters\Filter::make('with_hotels')
                    ->label('With Hotels')
                    ->query(fn ($query) => $query->has('products')),
                \Filament\Tables\Filters\Filter::make('with_both')
                    ->label('Used in Both')
                    ->query(function ($query) {
                        return $query->has('products')
                            ->has('hotelRooms');
                    }),
                \Filament\Tables\Filters\Filter::make('unused')
                    ->label('Unused Attributes')
                    ->query(function ($query) {
                        return $query->whereDoesntHave('products')
                            ->whereDoesntHave('hotelRooms');
                    }),
                \Filament\Tables\Filters\Filter::make('with_categories')
                    ->label('With Categories')
                    ->query(fn ($query) => $query->has('categories')),
                \Filament\Tables\Filters\Filter::make('without_categories')
                    ->label('Without Categories')
                    ->query(fn ($query) => $query->doesntHave('categories')),
                SelectFilter::make('category_filter')
                    ->label('Category Filter')
                    ->relationship('categories', 'name', fn ($query) => $query->orderBy('name'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => \Illuminate\Support\Str::of($record->name)->replace('_', ' ')->title())
                    ->multiple()
                    ->searchable()
                    ->placeholder('Select categories'),
            ], layout: \Filament\Tables\Enums\FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->actions([
                //                ActionGroup::make([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (ConfigAttribute $record): string => route('configurations.attributes.edit', $record))
                    ->visible(fn (ConfigAttribute $record) => Gate::allows('update', $record)),
                //                    DeleteAction::make()
                //                        ->requiresConfirmation()
                //                        ->action(fn (ConfigAttribute $record) => $record->delete())
                //                        ->visible(fn (ConfigAttribute $record) => Gate::allows('delete', $record)),
                //                ]),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ConfigAttribute::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete?')
                    ->modalDescription(function ($records) {
                        $warnings = [];
                        foreach ($records as $attribute) {
                            $productNames = ProductAttribute::where('config_attribute_id', $attribute->id)
                                ->with('product')
                                ->get()
                                ->pluck('product.name')
                                ->filter()
                                ->unique()
                                ->toArray();
                            if (count($productNames) > 0) {
                                $warnings[] = 'Attribute "'.$attribute->name.'" is used by products: '.implode(', ', $productNames);
                            }

                            $hotelRoomNames = HotelRoom::whereHas('attributes', function ($q) use ($attribute) {
                                $q->where('config_attribute_id', $attribute->id);
                            })
                                ->pluck('name')
                                ->unique()
                                ->toArray();
                            if (count($hotelRoomNames) > 0) {
                                $warnings[] = 'Attribute "'.$attribute->name.'" is used by hotel rooms: '.implode(', ', $hotelRoomNames);
                            }

                        }
                        if (count($warnings) > 0) {
                            return "Warning:\n".implode("\n", $warnings);
                        }

                        return 'This action will permanently delete the selected attributes.';
                    })
                    ->modalSubmitActionLabel('Delete')
                    ->modalCancelActionLabel('Cancel')
                    ->visible(fn () => Gate::allows('delete', ConfigAttribute::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.attributes.create'))
                    ->visible(fn () => Gate::allows('create', ConfigAttribute::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.attributes.attributes-table');
    }
}
