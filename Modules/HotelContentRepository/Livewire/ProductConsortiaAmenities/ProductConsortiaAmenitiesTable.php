<?php

namespace Modules\HotelContentRepository\Livewire\ProductConsortiaAmenities;

use App\Actions\ConfigConsortium\CreateConfigConsortium;
use App\Livewire\Configurations\Consortia\ConsortiaForm;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductConsortiaAmenity;

class ProductConsortiaAmenitiesTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public ?array $rateRoomIds = [];

    public ?int $roomId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->roomId = $roomId;
        $rate = HotelRate::where('id', $rateId)->first();
        $this->rateRoomIds = $rate ? $rate->rooms->pluck('id')->toArray() : [];
        $room = HotelRoom::where('id', $roomId)->first();
        $this->title = 'Consortia Amenities for '.$product->name;
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
            $this->title .= ' - Rate Name: '.$rate->name;
        }
        if ($this->roomId) {
            $this->title .= ' - Room ID: '.$this->roomId;
            $this->title .= ' - Room Name: '.$room->name;
        }
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),
            Hidden::make('room_id')->default($this->roomId),

            Grid::make(1)
                ->schema([
                    Select::make('consortia_id')
                        ->label('Consortia')
                        ->options(ConfigConsortium::all()->sortBy('name')->pluck('name', 'id'))
                        ->createOptionForm(Gate::allows('create', ConfigConsortium::class) ? ConsortiaForm::getSchema() : [])
                        ->createOptionUsing(function (array $data) {
                            /** @var CreateConfigConsortium $createConfigConsortium */
                            $createConfigConsortium = app(CreateConfigConsortium::class);
                            $consortia = $createConfigConsortium->create($data);
                            Notification::make()
                                ->title('Consortia created successfully')
                                ->success()
                                ->send();

                            return $consortia->name;
                        })
                        ->required(),
                    Textarea::make('description')->label('Description'),
                ]),
            Grid::make(2)->schema([
                DatePicker::make('start_date')
                    ->label('Travel Start Date')
                    ->native(false)
                    ->time(false)
                    ->format('Y-m-d')
                    ->displayFormat('m/d/Y')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Travel End Date')
                    ->native(false)
                    ->time(false)
                    ->format('Y-m-d')
                    ->displayFormat('m/d/Y'),
            ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductConsortiaAmenity::query()
                    ->where('product_id', $this->productId)
            )
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->rateId) {
                    $query->where(function ($q) {
                        $q->where('rate_id', $this->rateId)
                            ->orWhereNull('rate_id');
                    });
                    $query->where(function ($q) {
                        $q->whereIn('room_id', $this->rateRoomIds)
                            ->orWhereNull('room_id');
                    });
                } elseif ($this->roomId) {
                    $query->where(function ($q) {
                        $q->where('room_id', $this->roomId)
                            ->orWhereNull('rate_id')->whereNull('room_id');
                    });
                } else {
                    $query->whereNull('rate_id')->whereNull('room_id');
                }
            })
            ->deferLoading()
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $this->productId && $this->rateId && $this->rateId === $record->rate_id => 'Rate',
                            $this->productId && $this->roomId && $this->roomId === $record->room_id,
                            $this->productId && $this->rateId && $record->room_id !== null => 'Room',
                            default => 'Hotel',
                        };
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                        'success' => 'Room',
                    ]),
                TextColumn::make('consortia.name')->label('Consortia')->wrap(),
                TextColumn::make('start_date')->label('Start Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('description')->label('Description')->wrap(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions(
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form(fn ($record) => $this->schemeForm($record))
                        ->fillForm(function ($record) {
                            $data = $record->toArray();

                            return $data;
                        }),
                    DeleteAction::make()
                        ->label('Delete'),
                ])->visible(fn (ProductConsortiaAmenity $record): bool => ($this->rateId && $this->rateId === $record->rate_id) ||
                    ($this->roomId && $this->roomId === $record->room_id) ||
                    (! $this->rateId && ! $this->roomId)
                ),
            )
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-consortia-amenities-table');
    }
}
