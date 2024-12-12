<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Vendor;

class VendorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(Vendor::query()
                ->when(
                    auth()->user()->currentTeam && !auth()->user()->hasRole(RoleSlug::ADMIN->value),
                    fn ($q) => $q->where('id', auth()->user()->currentTeam->vendor_id)
                )
            )
            ->columns([
                IconColumn::make('verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->getStateUsing(function ($record) {
                        return $record->products()->count();
                    })
                    ->sortable(),
//                TextInputColumn::make('lat')
//                    ->label('Latitude')
//                    ->searchable()
//                    ->sortable()
//                    ->extraAttributes(['style' => 'width: 100%']),
//                TextInputColumn::make('lng')
//                    ->label('Longitude')
//                    ->searchable()
//                    ->sortable()
//                    ->extraAttributes(['style' => 'width: 100%']),
                TextInputColumn::make('website')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Vendor')
                    ->url(fn (Vendor $record): string => route('vendor-repository.edit', $record))
                    ->visible(fn (Vendor $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Vendor')
                    ->requiresConfirmation()
                    ->action(fn (Vendor $record) => $record->delete())
                    ->visible(fn (Vendor $record) => Gate::allows('delete', $record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => route('vendor-repository.create'))
                    ->tooltip('Add New Vendor')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->visible(fn () => Gate::allows('create', Vendor::class))
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.vendors.vendor-table');
    }
}
