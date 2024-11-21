<?php

namespace Modules\HotelContentRepository\Livewire\ProductDescriptiveContentSection;

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
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public function mount(int $productId)
    {
        $this->productId = $productId;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('product_id')
                ->label('Product')
                ->options(Product::pluck('name', 'id'))
                ->disabled(fn () => $this->productId)
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
            CustomRepeater::make('product_descriptive_contents')
                ->label('Product Descriptive Contents')
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
            ->query(ProductDescriptiveContentSection::with('content')->where('product_id', $this->productId))
            ->columns([
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
                    ->action(function (array $data, ProductDescriptiveContentSection $record) {
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
                        return $this->productId ? ['product_id' => $this->productId] : [];
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
        return view('livewire.products.product-descriptive-content-section-table');
    }

    protected function getFillFormData(int $recordId): array
    {
        $data = [
            'product_id' => $this->productId,
        ];

        if ($recordId) {
            $section = ProductDescriptiveContentSection::where('id', $recordId)->with('content')->first();

            if ($section) {
                $data['section_name'] = $section->section_name;
                $data['start_date'] = $section->start_date;
                $data['end_date'] = $section->end_date;
                $data['product_descriptive_contents'] = $section->content->map(function ($content) {
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
        if ($this->productId) $data['product_id'] = $this->productId;

        $section = ProductDescriptiveContentSection::updateOrCreate(
            [
                'id' => $recordId,
                'product_id' => $data['product_id']
            ],
            [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]
        );

        $section->content()->delete(); // Clear existing content

        foreach ($data['product_descriptive_contents'] as $contentData) {
            $section->content()->create($contentData);
        }
    }
}
