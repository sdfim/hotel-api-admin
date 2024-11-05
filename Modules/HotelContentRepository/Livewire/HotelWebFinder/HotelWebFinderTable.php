<?php

namespace Modules\HotelContentRepository\Livewire\HotelWebFinder;

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

    public function mount(?int $hotelId = null, ?HotelWebFinder $record = null)
    {
        $this->hotelId = $hotelId;
        if ($record) {
            $this->base_url = $record->base_url;
            $this->units = $record->units->toArray();
//            $this->finder = $record->finder;
            $this->updateFinder();
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
            TextInput::make('base_url')
                ->label('Base URL')
                ->afterStateHydrated(function ($component, $state) {
                    $this->base_url = $state;
                })
                ->required(),

            CustomRepeater::make('units')
                ->schema([
                    Select::make('field')
                        ->label('')
                        ->options([
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                            'duration' => 'Duration',
                            'number_of_rooms' => 'Number of Rooms',
                            'property_code' => 'Property Code',
                        ])
                        ->required(),
                    TextInput::make('value')
                        ->label('')
                        ->live()
//                        ->afterStateHydrated(function ($component, $state) {
//                            $this->updateFinder();
//                        })

                ])
                ->defaultItems(1)
                ->required()
                ->afterStateHydrated(function ($component, $state) {
                    $this->units = $state;
                    $this->updateFinder('form');
                })
//                ->beforeStateDehydrated(function ($state) {
//                    return json_encode($state);
//                })
                ->columns(2)
                ->columnSpan(1),
            TextInput::make('finder')
                ->label('Finder')
                ->live()
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
                TextInputColumn::make('base_url')
                    ->label('Base URL')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextInputColumn::make('finder')
                    ->label('Finder')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
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

    public function updatedUnits()
    {
        $this->updateFinder();
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

        $webFinder = HotelWebFinder::updateOrCreate(
            [
                'id' => $recordId,
                'hotel_id' => $data['hotel_id']
            ],
            [
                'base_url' => $data['base_url'],
                'finder' => $data['finder'],
            ]
        );

        $webFinder->units()->delete();

        foreach ($data['units'] as $unitData) {
            $webFinder->units()->create($unitData);
        }
    }

    protected function updateFinder(string $step = 'init')
    {
        $finder = $this->base_url . '?';
        if (!empty($this->units)) {
            foreach ($this->units as $index => $unit) {
                $finder .= ($index > 0 ? '&' : '') . $unit['value'] . '={' . $unit['field'] . '}';
            }
        }

        $this->finder = $finder;

        \Log::debug('updateFinder ' . $step , [
            'units' => $this->units,
            'base_url' => $this->base_url,
            'finder' => $this->finder,
        ]);
    }
}
