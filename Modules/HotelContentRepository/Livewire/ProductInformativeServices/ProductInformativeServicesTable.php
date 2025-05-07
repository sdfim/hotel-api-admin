<?php

namespace Modules\HotelContentRepository\Livewire\ProductInformativeServices;

use App\Helpers\ClassHelper;
use App\Livewire\Configurations\ServiceTypes\ServiceTypesForm;
use App\Models\Configurations\ConfigServiceType;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ProductServiceApplyTypeEnum;
use Modules\HotelContentRepository\Actions\ProductInformativeService\AddProductInformativeService;
use Modules\HotelContentRepository\Actions\ProductInformativeService\EditProductInformativeService;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServicesTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Product $product;

    public ?int $rateId = null;

    public ?array $rateRoomIds = [];

    public ?int $roomId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->product = $product;
        $this->rateId = $rateId;
        $this->roomId = $roomId;
        $rate = HotelRate::where('id', $rateId)->first();
        $this->rateRoomIds = $rate ? $rate->rooms->pluck('id')->toArray() : [];
        $room = HotelRoom::where('id', $roomId)->first();
        $this->title = 'Add On or Informational Service for '.$product->name;
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
            Hidden::make('product_id')->default($this->product->id),
            Hidden::make('rate_id')->default($this->rateId),
            Hidden::make('room_id')->default($this->roomId),

            Fieldset::make('General Setting')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->columnSpan(2),
                            Select::make('service_id')
                                ->label('Service Type')
                                ->options(ConfigServiceType::all()->sortBy('name')->pluck('name', 'id')->toArray())
                                ->createOptionForm(Gate::allows('create', ConfigServiceType::class) ? ServiceTypesForm::getSchema() : [])
                                ->createOptionUsing(function (array $data) {
                                    if (! isset($data['cost'])) {
                                        $data['cost'] = 0;
                                    }
                                    ConfigServiceType::create($data);
                                    Notification::make()
                                        ->title('Service created successfully')
                                        ->success()
                                        ->send();
                                })
                                ->required(),
                            TextInput::make('cost')
                                ->label('Total Rack')
                                ->numeric()
                                ->required(),
                            TextInput::make('total_net')
                                ->label('Total Net')
                                ->numeric(),
                            Select::make('apply_type')
                                ->label('Apply Type')
                                ->options([
                                    ProductServiceApplyTypeEnum::PER_SERVICE->value => 'Per Service',
                                    ProductServiceApplyTypeEnum::PER_PERSON->value => 'Per Person',
                                    ProductServiceApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                                    ProductServiceApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                                ])
                                ->reactive()
                                ->rules(['required']),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('currency')
                                ->label('Currency')
                                ->required()
                                ->options([
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                    'JPY' => 'JPY',
                                    'AUD' => 'AUD',
                                    'CAD' => 'CAD',
                                    'CHF' => 'CHF',
                                    'CNY' => 'CNY',
                                    'SEK' => 'SEK',
                                    'NZD' => 'NZD',
                                ]),
                            TimePicker::make('service_time')
                                ->label('Service Time')
                                ->format('h:i A'),
                            Select::make('collected_by')
                                ->label('Collected By')
                                ->options([
                                    'Direct' => 'Direct',
                                    'Vendor' => 'Vendor',
                                ])
                                ->required(),
                        ]),
                ]),

            Fieldset::make('Date Setting')
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Travel Start Date')
                        ->native(false),
                    DatePicker::make('end_date')
                        ->label('Travel End Date')
                        ->native(false),
                ]),

            Fieldset::make('Restrictions Setting')
                ->schema([
                    TextInput::make('age_from')
                        ->label('Age From')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('age_to')
                        ->label('Age To')
                        ->numeric()
                        ->minValue(0)
                        ->rule(function (Get $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $ageFrom = $get('age_from');
                                if (! is_null($ageFrom) && $value < $ageFrom) {
                                    $fail('The Age To must be greater than or equal to Age From.');
                                }
                            };
                        }),
                ]),

            Fieldset::make('Nights Setting')
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

            Fieldset::make('Additional Setting')
                ->schema([
                    Grid::make(4)
                        ->schema([
                            //                            Checkbox::make('show_service_on_pdf')
                            //                                ->label('Show Service on PDF'),
                            //                            Checkbox::make('show_service_data_on_pdf')
                            //                                ->label('Show Service Date on PDF'),
                            Checkbox::make('auto_book')
                                ->label('Mandatory'),
                            Checkbox::make('commissionable')
                                ->label('Commissionable'),
                        ]),
                ]),

            CustomRepeater::make('dynamicColumns')
                ->label('Dynamic Columns')
                ->defaultItems(0)
                ->schema([
                    Fieldset::make()
                        ->schema([
                            TextInput::make('name')
                                ->hiddenLabel()
                                ->placeholder('Name')
                                ->required(),

                            Textarea::make('value')
                                ->hiddenLabel()
                                ->placeholder('Value')
                                ->required()
                                ->rows(4),

                            Grid::make(3)
                                ->schema([
                                    Checkbox::make('show_on_invoice')
                                        ->label('Show on Invoice'),
                                    Checkbox::make('show_on_itinerary')
                                        ->label('Show on Itinerary'),
                                    Checkbox::make('show_on_vendor_manifest')
                                        ->label('Show on Vendor Manifest'),
                                ]),
                        ])
                        ->columns(1),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Add Ons or Informational Services')
            ->emptyStateDescription('Create an Add On or Informational Service to get started.')
            ->query(
                ProductInformativeService::query()
                    ->where('product_id', $this->product->id)
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
                            $this->product->id && $record->rate_id !== null => 'Rate',
                            $this->product->id && $record->room_id !== null => 'Room',
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
                            in_array($record->room_id, $this->rateRoomIds) => $record->room->external_code,
                            default => '',
                        };
                    }),
                TextColumn::make('name')->label('Name')->searchable()->wrap()->sortable(),
                TextColumn::make('service.name')->label('Service Type')->searchable()->sortable(),
                TextColumn::make('cost')->label('Total Rack')->searchable(),
                TextColumn::make('total_net')->label('Total Net')->searchable(),
                TextColumn::make('currency')->label('Currency')->searchable(),
                TextColumn::make('service_time')->label('Service Time')->searchable(),
                //                IconColumn::make('show_service_on_pdf')->label('Show on PDF')->boolean(),
                //                IconColumn::make('show_service_data_on_pdf')->label('Show Data on PDF')->boolean(),
                IconColumn::make('commissionable')->label('Commissionable')->boolean(),
                IconColumn::make('auto_book')->label('Mandatory')->boolean(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->tooltip('Edit Service')
                        ->form($this->schemeForm())
                        ->modalWidth('6xl')
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['dynamicColumns'] = $record->dynamicColumns->toArray();

                            return $data;
                        })
                        ->action(function ($data, $record) {
                            /** @var EditProductInformativeService $editProductInformativeService */
                            $editProductInformativeService = app(EditProductInformativeService::class);
                            $editProductInformativeService->updateWithDynamicColumns($record, $data);
                            Notification::make()
                                ->title('Service updated successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductInformativeService $record): bool => ($this->rateId && $this->rateId === $record->rate_id) ||
                    ($this->roomId && $this->roomId === $record->room_id) ||
                    (! $this->rateId && ! $this->roomId)
                ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->iconButton()
                    ->tooltip('Add New Service')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return [
                            'currency' => $this->product->default_currency,
                        ];
                    })
                    ->modalWidth('6xl')
                    ->createAnother(false)
                    ->action(function ($data) {
                        $data['product_id'] = $this->product->id;
                        /** @var AddProductInformativeService $addProductInformativeService */
                        $addProductInformativeService = app(AddProductInformativeService::class);
                        $addProductInformativeService->createWithDynamicColumns($data);
                        Notification::make()
                            ->title('Service created successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-informative-services-table');
    }
}
