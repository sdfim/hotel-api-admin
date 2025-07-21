<?php

namespace App\Livewire\Configurations\ServiceTypes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigServiceType;
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
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ServiceTypesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigServiceType::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
                TextColumn::make('cost'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigServiceType $record): string => route('configurations.service-types.edit', $record))
                        ->visible(fn (ConfigServiceType $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Are you sure you want to delete?')
                        ->modalDescription(function ($record) {
                            $warnings = [];
                            $informativeServices = ProductInformativeService::where('service_id', $record->id)
                                ->with(['product', 'room', 'rate'])
                                ->get();
                            $productNames = $informativeServices->pluck('product.name')->filter()->unique()->toArray();
                            $roomNames = $informativeServices->pluck('room.name')->filter()->unique()->toArray();

                            if (count($productNames) > 0) {
                                $warnings[] = 'Service type "' . $record->name . '" is used by products: ' . implode(', ', $productNames);
                            }

                            if (count($roomNames) > 0) {
                                $warnings[] = 'Service type "' . $record->name . '" is used by hotel rooms: ' . implode(', ', $roomNames);
                            }

                            if (count($warnings) > 0) {
                                return "Warning:\n" . implode("\n", $warnings);
                            }
                            
                            return 'This action will permanently delete the selected service type.';
                        })
                        ->action(fn (ConfigServiceType $record) => $record->delete())
                        ->visible(fn (ConfigServiceType $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.service-types.create'))
                    ->visible(fn () => Gate::allows('create', ConfigServiceType::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.service-types.service-types-table');
    }
}
