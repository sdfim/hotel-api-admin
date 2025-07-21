<?php

namespace App\Livewire\Configurations\Consortia;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
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
use Modules\HotelContentRepository\Models\ProductConsortiaAmenity;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\HotelRate;

class ConsortiaTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigConsortium::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigConsortium $record): string => route('configurations.consortia.edit', $record))
                        ->visible(fn (ConfigConsortium $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Are you sure you want to delete?')
                        ->modalDescription(function ($record) {
                            $warnings = [];
                            $amenities = ProductConsortiaAmenity::where('consortia_id', $record->id)
                                ->with(['product', 'room', 'rate'])
                                ->get();
                            $productNames = $amenities->pluck('product.name')->filter()->unique()->toArray();

                            if (count($productNames) > 0) {
                                $warnings[] = 'Consortium "' . $record->name . '" is used by products: ' . implode(', ', $productNames);
                            }
                            
                            if (count($warnings) > 0) {
                                return "Warning:\n" . implode("\n", $warnings);
                            }
                            return 'This action will permanently delete the selected consortium.';
                        })
                        ->action(fn (ConfigConsortium $record) => $record->delete())
                        ->visible(fn (ConfigConsortium $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.consortia.create'))
                    ->visible(fn () => Gate::allows('create', ConfigConsortium::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.consortia.consortia-table');
    }
}
