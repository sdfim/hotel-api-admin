<?php

namespace Modules\HotelContentRepository\Livewire\ProductDescriptiveContentSection;

use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesForm;
use App\Models\Configurations\ConfigDescriptiveType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public string $title;

    public function mount(Product $product, ?int $rateId = null): void
    {
        $this->productId = $product->id;
        $this->title = 'Descriptive Content for '.$product->name;
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Grid::make(2)
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Travel Start Date')
                        ->native(false)
                        ->nullable(),
                    DatePicker::make('end_date')
                        ->label('Travel End Date')
                        ->native(false)
                        ->nullable(),
                ]),
            Select::make('descriptive_type_id')
                ->label('Content')
                ->searchable()
                ->options(ConfigDescriptiveType::orderBy('name')->get()->mapWithKeys(function ($item) {
                    return [$item->id => "{$item->name} ({$item->type} | location: {$item->location->name})"];
                }))
                ->required()
                ->createOptionForm(Gate::allows('create', ConfigDescriptiveType::class) ? DescriptiveTypesForm::getSchema() : [])
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
            Grid::make(2)
                ->schema([
                    Textarea::make('document_description')
                        ->label('Document Description')
                        ->rows(3)
                        ->nullable(),
                    FileUpload::make('document_path')
                        ->label('Document')
                        ->directory('descriptive-documentation')
                        ->visibility('private')
                        ->downloadable()
                        ->nullable(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductDescriptiveContentSection::query()
                    ->where('product_id', $this->productId)
            )
            ->deferLoading()
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return ($this->productId) ? 'Rate' : 'Hotel';
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                    ]),
                TextColumn::make('start_date')->label('Travel Start Date')->date(),
                TextColumn::make('end_date')->label('Travel End Date')->date(),
                TextColumn::make('end_date')->label('Travel End Date')->date(),
                TextColumn::make('descriptiveType.name')
                    ->label('Content Section')
                    ->searchable(),
                TextColumn::make('value')->label('Value')->wrap()->limit(700),
                TextColumn::make('created_at')->label('Created At')->date(),
                TextColumn::make('document_description')->label('Document Description')->wrap(),
            ])
            ->actions(
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form(fn ($record) => $this->schemeForm($record))
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductDescriptiveContentSection $record): bool => $this->productId),
            )
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-descriptive-content-section-table');
    }
}
