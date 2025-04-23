<?php

namespace Modules\HotelContentRepository\Livewire\HotelWebFinder;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
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
use Modules\HotelContentRepository\Actions\HotelWebFinder\HotelWebFinderAction;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public $base_url;

    public $units = [];

    public string $title;

    public function mount(Hotel $hotel)
    {
        $this->hotelId = $hotel->id;
        $this->title = 'Website Search Generation for '.$hotel->product->name;
    }

    public function schemeForm($record = null): array
    {
        return [
            Hidden::make('hotel_id')->default($this->hotelId),
            TextInput::make('website')
                ->label('Website')
                ->required(),
            TextInput::make('base_url')
                ->label('Search Url Endpoint')
                ->live()
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, Set $set, ?HotelWebFinder $record) {
                    $this->base_url = $state;
                    $finder = $this->updateFinder($record);
                    $set('finder', $finder);
                })
                ->required(),

            CustomRepeater::make('units')
                ->label('Parameters')
                ->schema([
                    Select::make('field')
                        ->label('')
                        ->placeholder('Select Field')
                        ->options([
                            'search_start_travel_date_name' => 'Travel Start Date',
                            'search_end_travel_date_name' => 'Travel End Date',
                            'search_rooms_count_name' => 'Number of Rooms',
                            'search_property_identifier_name' => 'Property Code',
                            'search_adults_name' => 'Adults',
                            'search_children_name' => 'Children',
                            'search_nights_name' => 'Nights',
                        ])
                        ->required(),
                    TextInput::make('value')
                        ->label('')
                        ->placeholder('Value')
                        ->live(debounce: 500),
                    Select::make('type')
                        ->label('')
                        ->placeholder('Type')
                        ->options([
                            'm/d/y' => 'm/d/y',
                            'd/m/y' => 'd/m/y',
                            'Y-m-d' => 'Y-m-d',
                            'yy/mm/dd' => 'yy/mm/dd',
                        ])
                        ->visible(fn ($get) => in_array($get('field'), ['search_start_travel_date_name', 'search_end_travel_date_name'])),

                    TextInput::make('type')
                        ->label('')
                        ->placeholder('Type')
                        ->visible(fn ($get) => in_array($get('field'), ['search_property_identifier_name'])),
                ])
                ->defaultItems(1)
                ->required()
                ->afterStateUpdated(function ($state, Set $set, ?HotelWebFinder $record) {
                    $this->units = $state;
                    $finder = $this->updateFinder($record);
                    $set('finder', $finder);
                })
                ->columns(3)
                ->columnSpan(1),

            Textarea::make('finder')
                ->label('Finder')
                ->reactive()
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(function () {
                $query = HotelWebFinder::query()->with(['hotels', 'units']);
                if ($this->hotelId !== null) {
                    $query->whereHas('hotels', function ($query) {
                        $query->where('hotel_id', $this->hotelId);
                    });
                }

                return $query;
            })
            ->columns([
                TextColumn::make('website')
                    ->label('Website'),
                TextColumn::make('finder')
                    ->label('Finder/Pattern')
                    ->wrap(),
                TextColumn::make('example')
                    ->label('Example')
                    ->wrap()
                    ->limit(50)
                    ->icon('heroicon-o-clipboard')
                    ->copyable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Web Finder')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        return $this->getFillFormData($record->id);
                    })
                    ->modalHeading('Edit Web Finder')
                    ->action(function (array $data, HotelWebFinder $record) {
                        $this->saveOrUpdate($data, $record->id);
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Web Finder')
                    ->action(function (HotelWebFinder $record) {
                        $record->delete();
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->hotelId ? ['hotel_id' => $this->hotelId] : [];
                    })
                    ->tooltip('Add New Web Finder')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function (array $data) {
                        $this->saveOrUpdate($data);
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
                Action::make('attachCopy')
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->label('')
                    ->tooltip('Attach/Copy existing Web Finder')
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->modalHeading('Attach Existing Web Finder')
                    ->form([
                        Select::make('web_finder_id')
                            ->searchable()
                            ->label('Select Web Finder')
                            ->options(HotelWebFinder::all()->mapWithKeys(function ($item) {
                                return [$item->id => $item->type.' - '.$item->base_url];
                            }))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $webFinder = HotelWebFinder::find($data['web_finder_id']);
                        if ($webFinder) {
                            $webFinder->hotels()->syncWithoutDetaching([$this->hotelId]);
                        }
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-web-finder-table');
    }

    protected function getFillFormData(int $recordId): array
    {
        $data = [];

        if ($recordId) {
            $webFinder = HotelWebFinder::with(['units', 'hotels'])->find($recordId);

            if ($webFinder) {
                $data['hotel_id'] = $webFinder->hotels->first()->id ?? null;
                $data['base_url'] = $webFinder->base_url;
                $data['finder'] = $webFinder->finder;
                $data['website'] = $webFinder->website;
                $data['units'] = $webFinder->units->map(function ($unit) {
                    return [
                        'field' => $unit->field,
                        'value' => $unit->value,
                        'type' => $unit->type,
                    ];
                })->toArray();
            }
        }

        return $data;
    }

    protected function saveOrUpdate(array $data, ?int $recordId = null): void
    {
        /** @var HotelWebFinderAction $HotelWebFinderAction */
        $HotelWebFinderAction = app(HotelWebFinderAction::class);
        $HotelWebFinderAction->saveOrUpdate($data, $recordId, $this->hotelId);
    }

    protected function updateFinder(?HotelWebFinder $record = null): string
    {
        $preFinder = explode('?', $record?->finder);
        $finder = $this->base_url ? $this->base_url.'?' : ($preFinder[0] ? $preFinder[0].'?' : '');
        if (! empty($this->units)) {
            $step = 0;
            foreach ($this->units as $unit) {
                if ($unit['field'] === 'search_property_identifier_name') {
                    $finder .= ($step > 0 ? '&' : '').$unit['value'].'='.$unit['type'];
                } else {
                    $finder .= ($step > 0 ? '&' : '').$unit['value'].'={'.$unit['field'].(! empty($unit['type']) ? ':'.$unit['type'] : '').'}';
                }
                $step++;
            }
        } else {
            $finder .= $preFinder[1] ?? '';
        }

        return $finder;
    }
}
