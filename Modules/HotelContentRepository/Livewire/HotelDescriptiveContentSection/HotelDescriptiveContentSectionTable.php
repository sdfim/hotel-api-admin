<?php

namespace Modules\HotelContentRepository\Livewire\HotelDescriptiveContentSection;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigDescriptiveType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;

class HotelDescriptiveContentSectionTable extends Component implements HasForms, HasTable
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
        return [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
                ->disabled(fn () => $this->hotelId)
                ->required(),
//            TextInput::make('section_name')
//                ->label('Section Name')
//                ->required(),
            Grid::make(2)
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->native(false)
                        ->nullable(),
                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->native(false)
                        ->nullable(),
                ]),
            CustomRepeater::make('hotel_descriptive_contents')
                ->label('Hotel Descriptive Contents')
                ->schema([
                    Select::make('descriptive_type_id')
                        ->label('')
                        ->options(ConfigDescriptiveType::pluck('name', 'id'))
                        ->required(),
                    Textarea::make('value')
                        ->label(''),
                ])
            ->columns(2)
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(HotelDescriptiveContentSection::with('content')->where('hotel_id', $this->hotelId))
            ->columns([
//                TextColumn::make('section_name')->label('Section Name')->searchable(),
                TextColumn::make('start_date')->label('Start Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('content')
                    ->label('Content Section')
                    ->formatStateUsing(function ($state) {
                        $items = explode(', ', $state);
                        $string = '';
                        foreach ($items as $item) {
                            $dataItem = json_decode($item, true);
                            $string .= ConfigDescriptiveType::where('id', $dataItem['descriptive_type_id'])->first()->name . ': <b>' . $dataItem['value'] . '</b><br>';
                        }
                        return $string;
                    })
                    ->html()
                    ->searchable(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Section')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        return $this->getFillFormData($record->id);
                    })
                    ->modalHeading('Edit Section')
                    ->action(function (array $data, HotelDescriptiveContentSection $record) {
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
                    ->tooltip('Add New Section')
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
        return view('livewire.hotels.hotel-descriptive-content-section-table');
    }

    protected function getFillFormData(int $recordId): array
    {
        $data = [
            'hotel_id' => $this->hotelId,
        ];

        if ($recordId) {
            $section = HotelDescriptiveContentSection::where('id', $recordId)->with('content')->first();

            if ($section) {
                $data['section_name'] = $section->section_name;
                $data['start_date'] = $section->start_date;
                $data['end_date'] = $section->end_date;
                $data['hotel_descriptive_contents'] = $section->content->map(function ($content) {
                    return [
                        'descriptive_type_id' => $content->descriptive_type_id,
                        'value' => $content->value,
                    ];
                })->toArray();
            }
        }

        return $data;
    }

    protected function saveOrUpdate(array $data, ?int $recordId = null): void
    {
        if ($this->hotelId) $data['hotel_id'] = $this->hotelId;

        $section = HotelDescriptiveContentSection::updateOrCreate(
            [
                'id' => $recordId,
                'hotel_id' => $data['hotel_id']
            ],
            [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]
        );

        $section->content()->delete(); // Clear existing content

        foreach ($data['hotel_descriptive_contents'] as $contentData) {
            $section->content()->create($contentData);
        }
    }
}
