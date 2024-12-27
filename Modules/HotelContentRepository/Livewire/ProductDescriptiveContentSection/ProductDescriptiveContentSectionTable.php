<?php

namespace Modules\HotelContentRepository\Livewire\ProductDescriptiveContentSection;

use App\Helpers\ClassHelper;
use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesForm;
use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Enums\DescriptiveLocationEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasProductActions;

    public int $productId;
    public string $title;

    public function mount(int $productId)
    {
        $this->productId = $productId;
        $product = Product::find($productId);
        $this->title = 'Descriptive Content for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
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
            Grid::make(2)
                ->schema([
                    Select::make('descriptive_type_id')
                        ->label('Content')
                        ->options(ConfigDescriptiveType::pluck('name', 'id'))
                        ->required()
                        ->createOptionForm(DescriptiveTypesForm::getSchema())
                        ->createOptionUsing(function (array $data) {
                            ConfigDescriptiveType::create($data);
                            Notification::make()
                                ->title('DescriptiveType created successfully')
                                ->success()
                                ->send();
                        }),
                    Textarea::make('value')
                        ->label('Value')
                        ->rows(3)
                        ->required(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProductDescriptiveContentSection::where('product_id', $this->productId))
            ->columns([
                TextColumn::make('start_date')->label('Start Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('descriptiveType.name')
                    ->label('Content Section')
                    ->searchable(),
                TextColumn::make('value')->label('Value')->wrap(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
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
            $section = ProductDescriptiveContentSection::where('id', $recordId)->first();

            if ($section) {
                $data['section_name'] = $section->section_name;
                $data['start_date'] = $section->start_date;
                $data['end_date'] = $section->end_date;
                $data['descriptive_type_id'] = $section->descriptive_type_id;
                $data['value'] = $section->value;
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
                'descriptive_type_id' => $data['descriptive_type_id'],
                'value' => $data['value'],
            ]
        );
    }
}
