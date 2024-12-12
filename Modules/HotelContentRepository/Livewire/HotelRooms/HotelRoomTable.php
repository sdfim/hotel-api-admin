<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasProductActions;

    public ?int $hotelId = null;
    public string $title;

    public function mount(?int $hotelId = null, ?HotelRoom $record = null)
    {
        $this->hotelId = $hotelId;
        $hotel = Hotel::find($hotelId);
        $this->title = 'Website Search Generation for <h4>' . ($hotel ? $hotel->product->name : 'Unknown Hotel') . '</h4>';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm($record = null): array
    {
        return [
            Hidden::make('hotel_id')->default($this->hotelId),
            TextInput::make('hbsi_data_mapped_name')->label('External Code'),
            TextInput::make('name')->label('Name')->required(),
            Textarea::make('description')
                ->label('Description')
                ->required()
                ->rows(5),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->options(ImageGallery::pluck('gallery_name', 'id')),        ];
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
//                    ->searchable()
//                    ->sortable()
//                    ->wrap(),
                TextInputColumn::make('hbsi_data_mapped_name')
                    ->label('External Code')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => !Gate::allows('create', Hotel::class)),
                TextInputColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => !Gate::allows('create', Hotel::class)),
//                TextColumn::make('description')
//                    ->label('Description')
//                    ->searchable()
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
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.hotels.hotel-room-table');
    }
}
