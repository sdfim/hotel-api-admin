<?php

namespace Modules\HotelContentRepository\Livewire\HotelFeeTaxes;

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
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
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
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
//                ->when($this->hotelId, fn($select) => $select->searchable())
                ->required(),
            TextInput::make('name')->label('Name')->required(),
            TextInput::make('net_value')
                ->label('Net Value')
                ->numeric(2)
                ->required(),
            TextInput::make('rack_value')
                ->label('Rack Value')
                ->numeric(2)
                ->required(),
            TextInput::make('tax')
                ->label('Tax')
                ->numeric(2)
                ->required(),
            Select::make('type')
                ->label('Type')
                ->options([
                    'per person' => 'per person',
                    'per day' => 'per day',
                    'per night' => 'per night',
                    'per accommodation' => 'per accommodation',
                    'per stay' => 'per stay',
                ])
                ->required(),
            TextInput::make('fee_category')->label('Fee Category')->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelFeeTax::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextInputColumn::make('name')->label('Name')->searchable(),

                TextInputColumn::make('net_value')
                    ->label('Net Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                TextInputColumn::make('rack_value')
                    ->label('Rack Value')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                TextInputColumn::make('tax')
                    ->label('Tax')
                    ->sortable()
                    ->rules(['numeric', 'regex:/^\d+(\.\d{1,2})?$/']),

                SelectColumn::make('type')
                    ->label('Type')
                    ->options([
                        'per person' => 'per person',
                        'per day' => 'per day',
                        'per night' => 'per night',
                        'per accommodation' => 'per accommodation',
                        'per stay' => 'per stay',
                    ])
                    ->sortable(),
                TextInputColumn::make('fee_category')->label('Fee Category')->sortable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Fee Tax')
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
                    ->tooltip('Add New Fee')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-fee-tax-table');
    }

    public function save($record)
    {
        $this->validate([
            'net_value' => 'required|numeric',
            'rack_value' => 'required|numeric',
            'tax' => 'required|numeric',
        ]);

        $record->update($this->getValidatedData());

        session()->flash('message', 'Данные успешно сохранены.');
    }

    protected function getValidatedData(): array
    {
        return [
            'net_value' => $this->net_value,
            'rack_value' => $this->rack_value,
            'tax' => $this->tax,
        ];
    }
}
