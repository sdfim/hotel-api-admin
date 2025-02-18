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
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null): void
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->title = 'Descriptive Content for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),
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
                        ->options(ConfigDescriptiveType::get()->mapWithKeys(function ($item) {
                            if ($item->name !== $item->type) {
                                return [$item->id => "{$item->name} ({$item->type})"];
                            }
                            return [$item->id => $item->name];
                        }))
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
            Grid::make(2)
                ->schema([
                    Textarea::make('document_description')
                        ->label('Document Description')
                        ->rows(3)
                        ->nullable(),
                    FileUpload::make('document_path')
                        ->label('Document')
                        ->disk('public')
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
            ->query(ProductDescriptiveContentSection::where('product_id', $this->productId)
                ->where('rate_id', $this->rateId))
            ->columns([
                TextColumn::make('start_date')->label('Start Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('descriptiveType.name')
                    ->label('Content Section')
                    ->searchable(),
                TextColumn::make('value')->label('Value')->wrap(),
                TextColumn::make('created_at')->label('Created At')->date(),
                TextColumn::make('document_description')->label('Document Description')->wrap(),
            ])
            ->actions(array_merge(
                [Action::make('download')
                    ->icon('heroicon-s-arrow-down-circle')
                    ->color('success')
                    ->label('Download Document')
                    ->visible(fn (ProductDescriptiveContentSection $record) => ! is_null($record->document_path))
                    ->action(function (ProductDescriptiveContentSection $record) {
                        $filePath = $record->document_path;
                        if (Storage::disk('public')->exists($filePath)) {
                            return response()->download(
                                Storage::disk('public')->path($filePath),
                                basename($filePath)
                            );
                        }
                        Notification::make()
                            ->title('File not found')
                            ->danger()
                            ->send();

                        return false;
                    })],
                $this->getActions()
            ))
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render()
    {
        return view('livewire.products.product-descriptive-content-section-table');
    }
}
