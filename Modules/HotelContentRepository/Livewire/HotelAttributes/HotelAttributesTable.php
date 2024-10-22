<?php

namespace Modules\HotelContentRepository\Livewire\HotelAttributes;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\HotelAttribute;

class HotelAttributesTable extends Component implements HasForms, HasTable
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
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            TextInput::make('name')->label('Attribute Name')->required(),
            TextInput::make('attribute_value')->label('Value')->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelAttribute::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('name')->label('Attribute Name')->searchable(),
                TextColumn::make('attribute_value')->label('Value')->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Affiliation')
                ->form($this->schemeForm()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->form($this->schemeForm()),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-attributes-table');
    }
}
