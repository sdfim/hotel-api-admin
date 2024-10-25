<?php

namespace Modules\HotelContentRepository\Livewire\HotelAttributes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Select;
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
use Modules\HotelContentRepository\Models\Hotel;
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
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
//                ->when($this->hotelId, fn($select) => $select->searchable())
                ->required(),
            Select::make('attribute_id')
                ->label('Attribute')
                ->options(ConfigAttribute::pluck('name', 'id'))
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelAttribute::with('attribute')->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('attribute.name')->label('Attribute Name')->searchable(),
                TextColumn::make('attribute.default_value')->label('Value')->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Attribute')
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
                    ->tooltip('Add New Attribute')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-attributes-table');
    }
}
