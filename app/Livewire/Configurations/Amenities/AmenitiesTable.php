<?php

namespace App\Livewire\Configurations\Amenities;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAmenity;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\HotelContentRepository\Models\HotelRoom;

class AmenitiesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigAmenity::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                //                ActionGroup::make([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (ConfigAmenity $record): string => route('configurations.amenities.edit', $record))
                    ->visible(fn (ConfigAmenity $record) => Gate::allows('update', $record)),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ConfigAmenity::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete?')
                    ->modalDescription(function ($records) {
                        $warnings = [];
                        foreach ($records as $amenity) {
                            $affiliations = ProductAffiliation::whereHas('amenities', function ($q) use ($amenity) {
                                $q->where('amenity_id', $amenity->id);
                            })->with(['product', 'room'])->get();
                            if ($affiliations->count() > 0) {
                                $products = $affiliations->pluck('product.name')->filter()->unique()->toArray();
                                $rooms = $affiliations->pluck('room.name')->filter()->unique()->toArray();
                                if (count($products) > 0) {
                                    $warnings[] = 'Amenity "' . $amenity->name . '" is used by products: ' . implode(', ', $products);
                                }
                                if (count($rooms) > 0) {
                                    $warnings[] = 'Amenity "' . $amenity->name . '" is used by hotel rooms: ' . implode(', ', $rooms);
                                }
                            }
                        }
                        if (count($warnings) > 0) {
                            return "Warning:\n" . implode("\n", $warnings);
                        }
                        return 'This action will permanently delete the selected amenities.';
                    })
                    ->modalSubmitActionLabel('Delete')
                    ->modalCancelActionLabel('Cancel')
                    ->visible(fn () => Gate::allows('delete', ConfigAmenity::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.amenities.create'))
                    ->visible(fn () => Gate::allows('create', ConfigAmenity::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.amenities.amenities-table');
    }
}
