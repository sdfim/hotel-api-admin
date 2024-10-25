<?php

namespace Modules\HotelContentRepository\Livewire\HotelAgeRestriction;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;

class HotelAgeRestrictionTable extends Component implements HasForms, HasTable
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
            Select::make('age_restriction_id')
                ->label('Age Restriction')
                ->options(HotelAgeRestrictionType::pluck('name', 'id'))
                ->required(),
            TextInput::make('value')
                ->label('Value')
                ->required(),
            Checkbox::make('active')
                ->label('Active')
                ->default(true)
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelAgeRestriction::with('restrictionType')->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('restrictionType.name')->label('Restriction Name')->searchable(),
                TextColumn::make('restrictionType.description')->label('Description')->searchable(),
                TextColumn::make('value')->label('Value')->searchable(),
                BooleanColumn::make('active')
                    ->label('Is Active')
                    ->searchable(),            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Restriction')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        return [
                            'hotel_id' => $record->hotel_id,
                            'age_restriction_id' => $record->restriction_type_id,
                            'value' => $record->value,
                            'active' => $record->active,
                        ];
                    }),
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
                    ->tooltip('Add New Restriction')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function ($data) {
                        HotelAgeRestriction::create([
                            'hotel_id' => $data['hotel_id'],
                            'restriction_type_id' => $data['age_restriction_id'],
                            'value' => $data['value'],
                            'active' => $data['active'],
                        ]);

                        // Optionally, return a success message or perform additional operations
                        session()->flash('success', 'New restriction added successfully.');
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-age-restriction-table');
    }
}
