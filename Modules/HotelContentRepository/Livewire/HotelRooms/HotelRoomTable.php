<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

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
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public function mount(?int $hotelId = null)
    {
        $this->hotelId = $hotelId;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('hotel_id')->label('Hotel ID')->required(),
                TextInput::make('hbs_data_mapped_name')->label('HBS Data Mapped Name')->required(),
                TextInput::make('name')->label('Name')->required(),
                TextInput::make('description')->label('Description')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = HotelRoom::query();
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('hotel_id')->label('Hotel ID')->sortable(),
                TextColumn::make('hbs_data_mapped_name')->label('HBS Data Mapped Name')->searchable(),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('description')->label('Description')->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Hotel Room'),
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
        return view('livewire.hotels.hotel-room-table');
    }
}
