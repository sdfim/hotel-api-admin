<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public function mount(?int $hotelId = null, ?HotelRoom $record = null)
    {
        $this->hotelId = $hotelId;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm($record = null): array
    {
        return [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::with('product')->get()->pluck('product.name', 'id'))
                ->disabled(fn () => $this->hotelId)
                ->required(),
            TextInput::make('hbsi_data_mapped_name')->label('HBSI Data Mapped Name'),
            TextInput::make('name')->label('Name')->required(),
            Textarea::make('description')
                ->label('Description')
                ->required()
                ->rows(5),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->options(ImageGallery::hasHotelRoom($this->hotelId)->pluck('gallery_name', 'id')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(function () {
                $query = HotelRoom::query()->with(['hotel', 'galleries']);
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }
                return $query;
            })
            ->columns([
//                TextColumn::make('id')->label('ID')->sortable(),
//                TextColumn::make('hotel.name')
//                    ->label('Hotel Name')
//                    ->searchable(isIndividual: true)
//                    ->sortable()
//                    ->wrap(),
                TextInputColumn::make('hbsi_data_mapped_name')
                    ->label('HBSI Data Mapped Name')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextInputColumn::make('name')
                    ->label('Name')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
//                TextColumn::make('description')
//                    ->label('Description')
//                    ->searchable(isIndividual: true)
//                    ->sortable()
//                    ->wrap()
//                    ->extraAttributes(['class' => 'scrollable-column']),
                TextColumn::make('created_at')->label('Created At')->date(),
//                TextColumn::make('galleries')
//                    ->label('Galleries')
//                    ->formatStateUsing(function ($record) {
//                        return $record->galleries->pluck('gallery_name')->implode(', ');
//                    })
//                    ->wrap(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Hotel Room')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['galleries'] = $record->galleries->pluck('gallery_name')->toArray();
                        $data['hotel_id'] = $record->hotel->id;
                        return $data;
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
                    ->action(function ($data) {
                        if ($this->hotelId) $data['hotel_id'] = $this->hotelId;
                        HotelRoom::create($data);
                    })
                    ->tooltip('Add New Room')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-room-table');
    }
}
