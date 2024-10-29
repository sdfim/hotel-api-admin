<?php

namespace Modules\HotelContentRepository\Livewire\HotelAffiliations;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
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
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return  [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
//                ->when($this->hotelId, fn($select) => $select->searchable())
                ->required(),
            Select::make('affiliation_name')
                ->label('Affiliation Name')
                ->options([
                    'UJV Exclusive Amenities' => 'UJV Exclusive Amenities',
                    'Consortia Inclusions' => 'Consortia Inclusions',
                ])
                ->required(),
            Select::make('combinable')
                ->label('Combinable')
                ->options([
                    1 => 'Yes',
                    0 => 'No',
                ])
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelAffiliation::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('affiliation_name')->label('Affiliation Name')->searchable(),
                IconColumn::make('combinable')
                    ->label('Combinable')
                    ->boolean(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Affiliation')
                    ->form($this->schemeForm())
                    ->modalHeading('Edit Affiliation'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->hotelId ? ['hotel_id' => $this->hotelId] : [];
                    })
                    ->tooltip('Add New Affiliation')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-affiliations-table');
    }
}
