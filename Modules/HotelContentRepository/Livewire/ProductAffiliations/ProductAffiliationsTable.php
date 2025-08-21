<?php

namespace Modules\HotelContentRepository\Livewire\ProductAffiliations;

use App\Actions\ConfigAmenity\CreateConfigAmenity;
use App\Helpers\ClassHelper;
use App\Livewire\Configurations\Amenities\AmenitiesForm;
use App\Models\Configurations\ConfigAmenity;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\Utils\Tools;

class ProductAffiliationsTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public Product $product;

    public ?int $roomId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->product = $product;
        $this->productId = $product->id;
        $this->roomId = $roomId;
        $room = HotelRoom::where('id', $roomId)->first();
        $this->title = 'Amenities for '.$product->name;
        if ($this->roomId) {
            $this->title .= ' - Room ID: '.$this->roomId;
            $this->title .= ' - Room Name: '.$room->name;
        }
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('room_id')->default($this->roomId),

            Grid::make(2)->schema([
                DatePicker::make('start_date')
                    ->label('Travel Start Date')
                    ->native(false)
                    ->time(false)
                    ->format('Y-m-d')
                    ->displayFormat('m/d/Y')
                    ->default(now())
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Travel End Date')
                    ->native(false)
                    ->time(false)
                    ->format('Y-m-d')
                    ->displayFormat('m/d/Y'),
            ]),

            CustomRepeater::make('amenities')
                ->relationship('amenities')
                ->label('Amenities')
                ->schema([
                    Fieldset::make('')
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    Select::make('amenity_id')
                                        ->label('Amenity')
                                        ->options(ConfigAmenity::all()->sortBy('name')->pluck('name', 'id'))
                                        ->createOptionForm(Gate::allows('create', ConfigAmenity::class) ? AmenitiesForm::getSchema() : [])
                                        ->createOptionUsing(function (array $data) {
                                            /** @var CreateConfigAmenity $createConfigAmenity */
                                            $createConfigAmenity = app(CreateConfigAmenity::class);
                                            $amenity = $createConfigAmenity->create($data);
                                            Notification::make()
                                                ->title('Department created successfully')
                                                ->success()
                                                ->send();

                                            return $amenity->name;
                                        })
                                        ->required()
                                        ->columnSpan(1),
                                    Textarea::make('description')
                                        ->label('Description')
                                        ->rows(2)
                                        ->columnSpan(1),
                                ]),
                            Grid::make(6)
                                ->schema([
                                    Select::make('consortia')
                                        ->label('Consortia')
                                        ->multiple()
                                        ->options(ConfigConsortium::all()->sortBy('name')->pluck('name', 'name'))
                                        ->required()
                                        ->columnSpan(2),
                                    Select::make('is_paid')
                                        ->label('Is Paid')
                                        ->options([
                                            0 => 'No',
                                            1 => 'Yes',
                                        ])
                                        ->reactive()
                                        ->required(),
                                    TextInput::make('price')
                                        ->label('Net Price')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step('0.01')
                                        ->visible(fn ($get) => $get('is_paid') == 1),
                                    Select::make('currency')
                                        ->label('Currency')
                                        ->required()
                                        ->searchable()
                                        ->default($this->product->default_currency ?? 'USD')
                                        ->options(Tools::getCurrencyOptions())
                                        ->visible(fn ($get) => $get('is_paid') == 1),
                                    Select::make('apply_type')
                                        ->label('Apply Type')
                                        ->options([
                                            ProductApplyTypeEnum::PER_ROOM->value => 'Per Room',
                                            ProductApplyTypeEnum::PER_PERSON->value => 'Per Person',
                                            ProductApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                                        ])
                                        ->reactive()
                                        ->visible(fn ($get) => $get('is_paid') == 1),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('min_night_stay')
                                        ->label('Min Night Stay')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step('1'),
                                    TextInput::make('max_night_stay')
                                        ->label('Max Night Stay')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step('1'),
                                ]),
                            Grid::make(1)
                                ->schema([
                                    Select::make('priority_rooms')
                                        ->label('Rooms')
                                        ->multiple()
                                        ->searchable()
                                        ->options(HotelRoom::whereHas('rates', function ($query) {
                                            $query->where('pd_hotel_rates.id', $this->rateId);
                                        })->pluck('name', 'id')
                                        )
                                        ->reactive()
                                        ->visible(fn () => $this->rateId !== null),
                                ]),
                        ]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAffiliation::query()
                    ->where('product_id', $this->productId)
            )
            ->deferLoading()
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $this->productId && $record->rate_id !== null => 'Rate',
                            $this->productId && $record->room_id !== null => 'Room',
                            default => 'Hotel',
                        };
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                        'success' => 'Room',
                    ]),
                TextColumn::make('code_entity')
                    ->toggleable()
                    ->label('Code')
                    ->getStateUsing(function ($record) {
                        return match (true) {
                            $record->rate_id !== null => $record->rate?->code,
                            $record->room_id !== null => $record->room->external_code,
                            default => '',
                        };
                    }),
                TextColumn::make('amenities')
                    ->label('Amenities')
                    ->wrap()
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->amenities->map(function ($amenity) {
                            return "Amenity: {$amenity->amenity->name},
                            Consortia: ".implode(', ', $amenity->consortia).',
                            Is Paid: '.($amenity->is_paid ? 'Yes' : 'No').', Price: '.($amenity->price ?? 'N/A').
                                ', Min Night Stay: '.($amenity->min_night_stay ?? 'N/A').
                                ', Max Night Stay: '.($amenity->max_night_stay ?? 'N/A');
                        })->implode('<br>');
                    }),
                TextColumn::make('start_date')->label('Travel Start Date')->date(),
                TextColumn::make('end_date')->label('Travel End Date')->date(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions(
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form(fn ($record) => $this->schemeForm($record))
                        ->modalWidth('6xl')
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['amenities'] = $record->amenities->pluck('id')->toArray();

                            return $data;
                        })
                        ->action(function ($record, $data) {
                            $record->update($data);
                            if (isset($data['amenities'])) {
                                $record->amenities()->sync($data['amenities']);
                            }
                        })
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductAffiliation $record): bool => ($this->rateId && $this->rateId === $record->rate_id)
                    || ($this->roomId && $this->roomId === $record->room_id)
                    || (! $this->rateId && ! $this->roomId && $record->room_id === null && $record->rate_id === null)
                ),
            )
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->modalWidth('5xl')
                    ->tooltip('Add New Entity')
                    ->icon('heroicon-o-plus')
                    ->visible(fn () => Gate::allows('create', Product::class))
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-affiliations-table');
    }
}
