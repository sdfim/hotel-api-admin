<?php

namespace Modules\HotelContentRepository\Livewire\HotelFeeTaxes;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\HotelFeeTax;

class HotelFeeTaxTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $hotelId;

    public function mount(int $hotelId)
    {
        $this->hotelId = $hotelId;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Name')->required(),
                TextInput::make('hotel_id')->label('Hotel ID')->required(),
                TextInput::make('net_value')->label('Net Value')->required(),
                TextInput::make('rack_value')->label('Rack Value')->required(),
                TextInput::make('tax')->label('Tax')->required(),
                TextInput::make('type')->label('Type')->required(),
                TextInput::make('fee_category')->label('Fee Category')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelFeeTax::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('hotel_id')->label('Hotel ID')->sortable(),
                TextColumn::make('net_value')->label('Net Value')->sortable(),
                TextColumn::make('rack_value')->label('Rack Value')->sortable(),
                TextColumn::make('tax')->label('Tax')->sortable(),
                TextColumn::make('type')->label('Type')->sortable(),
                TextColumn::make('fee_category')->label('Fee Category')->sortable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Fee Tax'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-fee-tax-table');
    }
}
