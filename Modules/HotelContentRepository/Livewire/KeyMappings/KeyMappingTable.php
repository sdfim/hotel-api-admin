<?php

namespace Modules\HotelContentRepository\Livewire\KeyMappings;

use App\Models\Property;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class KeyMappingTable extends Component implements HasForms, HasTable
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
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
                ->required(),
            TextInput::make('name')->label('Name')->required(),
            Select::make('key_mapping_owner_id')
                ->label('Key Mapping Owner')
                ->options(KeyMappingOwner::pluck('name', 'id'))
                ->required(),
            TextInput::make('key_id')->label('Key ID')->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KeyMapping::with('keyMappingOwner')->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextInputColumn::make('name')->label('Name')->searchable(),
                TextInputColumn::make('key_id')->label('Key id')->searchable(),
                TextColumn::make('keyMappingOwner.name')->label('Owner'),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Key Mapping')
                    ->form($this->schemeForm()),
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
                    ->tooltip('Add New Mapping')
                    ->label('+')
                    ->extraAttributes(['class' => 'btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600']),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.key-mapping-table');
    }
}
