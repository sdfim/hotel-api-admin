<?php

namespace Modules\HotelContentRepository\Livewire\HotelAffiliations;

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
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class HotelAffiliationsTable extends Component implements HasForms, HasTable
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
                TextInput::make('affiliation_name')->label('Affiliation Name')->required(),
                TextInput::make('combinable')->label('Combinable')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelAffiliation::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('affiliation_name')->label('Affiliation Name')->searchable(),
                BooleanColumn::make('combinable')->label('Combinable'),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                ->label('')
                ->tooltip('Edit Affiliation'),
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
        return view('livewire.hotels.hotel-affiliations-table');
    }
}
