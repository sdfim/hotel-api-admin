<?php

namespace Modules\HotelContentRepository\Livewire\HotelWebFinder;

use App\Helpers\ClassHelper;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;
use Modules\HotelContentRepository\Models\Product;

class HotelWebFinderTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;
    public $base_url;
    public $units = [];
    public string $title;

    public function mount(?int $hotelId = null)
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
            TextInput::make('type')
                ->label('Search Type')
                ->required(),
            TextInput::make('base_url')
                ->label('Base URL')
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
                        ->options([
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                            'destination' => 'Destination',
                            'number_of_rooms' => 'Number of Rooms',
                            'property_code' => 'Property Code',
                        ])
                        ->required(),
                    TextInput::make('value')
                        ->label('')
                        ->live(debounce: 500)
                ])
                ->defaultItems(1)
                ->required()
                ->afterStateUpdated(function ($state, Set $set, ?HotelWebFinder $record) {
                    $this->units = $state;
                    $finder = $this->updateFinder($record);
                    $set('finder', $finder);
                })
                ->columns(2)
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
                TextColumn::make('type')
                    ->label('Search Type'),
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
            ->bulkActions([
//                DeleteBulkAction::make(),
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
                                return [$item->id => $item->type . ' - ' . $item->base_url];
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
                $data['type'] = $webFinder->type;
                $data['units'] = $webFinder->units->map(function ($unit) {
                    return [
                        'field' => $unit->field,
                        'value' => $unit->value,
                    ];
                })->toArray();
            }
        }

        return $data;
    }

    protected function saveOrUpdate(array $data, ?int $recordId = null): void
    {
        $startDate = Carbon::now()->addMonth()->format('Y-m-d');
        $endDate = Carbon::parse($startDate)->addDays(7)->format('Y-m-d');
        $hotel = Hotel::find($this->hotelId);
        $destination = Arr::get($hotel?->address, 'city', 'New+York');
        $numberOfRooms = '1';

        $data['example'] = str_replace(
            ['{start_date}', '{end_date}', '{destination}', '{number_of_rooms}'],
            [$startDate, $endDate, $destination, $numberOfRooms],
            $data['finder']
        );

        $webFinder = HotelWebFinder::updateOrCreate(
            ['id' => $recordId],
            [
                'base_url' => $data['base_url'],
                'finder' => $data['finder'],
                'type' => $data['type'],
                'example' => $data['example'],
            ]
        );

        $webFinder->hotels()->sync([$this->hotelId]);

        $webFinder->units()->delete();

        foreach ($data['units'] as $unitData) {
            $webFinder->units()->create($unitData);
        }
    }

    protected function updateFinder(?HotelWebFinder $record = null): string
    {
        $preFinder = explode('?', $record?->finder);
        $finder = $this->base_url ? $this->base_url . '?' : ($preFinder[0] ? $preFinder[0] . '?' : '');
        if (!empty($this->units)) {
            $step = 0;
            foreach ($this->units as $unit) {
                $finder .= ($step > 0 ? '&' : '') . $unit['value'] . '={' . $unit['field'] . '}';
                $step++;
            }
        } else {
            $finder .= $preFinder[1] ?? '';
        }

        return $finder;
    }
}
