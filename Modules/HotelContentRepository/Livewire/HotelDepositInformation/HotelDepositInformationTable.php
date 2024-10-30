<?php

namespace Modules\HotelContentRepository\Livewire\HotelDepositInformation;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelDepositInformation;

class HotelDepositInformationTable extends Component implements HasForms, HasTable
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
                ->required(),
            TextInput::make('days_departure')
                ->label('Days Prior to Departure')
                ->required()
                ->numeric(),
            TextInput::make('per_channel')
                ->label('Per Channel'),
            TextInput::make('per_room')
                ->label('Per Room'),
            TextInput::make('per_rate')
                ->label('Per Rate'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelDepositInformation::query()->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('days_departure')->label('Days Prior to Departure')->searchable(),
                TextColumn::make('per_channel')->label('Per Channel')->searchable(),
                TextColumn::make('per_room')->label('Per Room')->searchable(),
                TextColumn::make('per_rate')->label('Per Rate')->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Deposit Information')
                    ->form($this->schemeForm())
                    ->modalHeading('Edit Deposit Information'),
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
                    ->tooltip('Add New Deposit Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-deposit-information-table');
    }
}
