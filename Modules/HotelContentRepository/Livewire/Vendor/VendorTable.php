<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Helpers\ClassHelper;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Vendor;

class VendorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(Vendor::query())
            ->columns([
                BooleanColumn::make('verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),
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
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.vendors.vendor-table');
    }
}
