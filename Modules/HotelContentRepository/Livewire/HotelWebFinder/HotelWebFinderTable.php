<?php

namespace Modules\HotelContentRepository\Livewire\HotelWebFinder;

use App\Helpers\ClassHelper;
use Carbon\Carbon;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
    public $finder;

    public function mount(?int $hotelId = null)
    {
        $this->hotelId = $hotelId;
        $record = HotelWebFinder::where('hotel_id', $hotelId)->first();
        if ($record) {
            $this->base_url = $record->base_url;
            $this->units = $record->units->toArray();
            $this->finder = $record->finder;
        }
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
                ->options(Hotel::pluck('name', 'id'))
                ->disabled(fn () => $this->hotelId)
                ->required(),
            TextInput::make('type')
                ->label('Search Type')
                ->required(),
            TextInput::make('base_url')
                ->label('Base URL')
                ->live()
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, Set $set) {
                    $this->base_url = $state;
                    $finder = $this->updateFinder();
                    $set('finder', $finder);
                })
                ->required(),

            CustomRepeater::make('units')
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
                ->afterStateUpdated(function ($state, Set $set) {
                    $this->units = $state;
                    $finder = $this->updateFinder();
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
                $query = HotelWebFinder::query()->with(['hotel']);
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('type')
                    ->label('Search Type')
                    ->sortable(),
                TextColumn::make('finder')
                    ->label('Finder/Pattern')
                    ->wrap(),
                TextColumn::make('example')
                    ->label('Example')
                    ->url(fn($record) => $record->example)
                    ->openUrlInNewTab()
                    ->wrap(),
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
                    ->tooltip('Add New Web Finder')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->action(function (array $data) {
                        $this->saveOrUpdate($data);
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-web-finder-table');
    }

    protected function getFillFormData(int $recordId): array
    {
        $data = [
            'hotel_id' => $this->hotelId,
        ];

        if ($recordId) {
            $webFinder = HotelWebFinder::where('id', $recordId)->with('units')->first();

            if ($webFinder) {
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
        if ($this->hotelId) $data['hotel_id'] = $this->hotelId;

        $startDate = Carbon::now()->addMonth()->format('Y-m-d');
        $endDate = Carbon::parse($startDate)->addDays(7)->format('Y-m-d');
        $destination = 'New+York';
        $numberOfRooms = '1';

        $data['example'] = str_replace(
            ['{start_date}', '{end_date}', '{destination}', '{number_of_rooms}'],
            [$startDate, $endDate, $destination, $numberOfRooms],
            $data['finder']
        );

        $webFinder = HotelWebFinder::updateOrCreate(
            [
                'id' => $recordId,
                'hotel_id' => $data['hotel_id']
            ],
            [
                'base_url' => $data['base_url'],
                'finder' => $data['finder'],
                'type' => $data['type'],
                'example' => $data['example'],
            ]
        );

        $webFinder->units()->delete();

        foreach ($data['units'] as $unitData) {
            $webFinder->units()->create($unitData);
        }
    }

    protected function updateFinder()
    {
        $preFinder = explode('?', $this->finder);
        $finder = $this->base_url ? $this->base_url . '?' : ($preFinder[0] ? $preFinder[0] . '?' : '');
        if (!empty($this->units)) {
            $step = 0;
            foreach ($this->units as $unit) {
                \Log::debug('updateFinder ' . $step);
                $finder .= ($step > 0 ? '&' : '') . $unit['value'] . '={' . $unit['field'] . '}';
                $step++;
            }
        } else {
            $finder .= $preFinder[1] ?? '';
        }

        return $finder;
    }
}
