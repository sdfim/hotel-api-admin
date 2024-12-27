<?php

namespace Modules\HotelContentRepository\Livewire\ProductAffiliations;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationsTable extends Component implements HasForms, HasTable
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
        $this->title = 'Affiliations for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function schemeForm(): array
    {
        return  [
            Hidden::make('product_id')->default($this->productId),

            Fieldset::make('UJV Exclusive Amenities')
                ->schema([
                    Textarea::make('combinable')
                        ->label('Combinable'),
                    Textarea::make('non_combinable')
                        ->label('Non Combinable'),
                ])
            ->columns(1),
            Fieldset::make('Consortia Inclusions')
                ->schema([
                    CustomRepeater::make('details')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('consortia_id')
                                    ->label('Consortia')
                                    ->options(ConfigConsortium::pluck('name', 'id'))
                                    ->required(),
                                Select::make('combinable')
                                    ->label('Combinable')
                                    ->options([
                                        1 => 'Yes',
                                        0 => 'No',
                                    ]),
                                ]),
                            Grid::make(2)->schema([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->native(false)
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->native(false)
                                    ->required(),
                            ]),
                            Grid::make(1)->schema([
                                Textarea::make('description')
                                    ->label('Description')
                                    ->required(),
                            ]),
                        ])
                        ->minItems(1),
                ])->columns(1),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductAffiliation::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('combinable_non_combinable')
                    ->label('UJV Exclusive Amenities')
                    ->getStateUsing(function ($record) {
                        return 'Combinable: ' . $record->combinable . '<br>Non Combinable: ' . $record->non_combinable;
                    })
                    ->html(),
                TextColumn::make('details')
                    ->label('Consortia Inclusions')
                    ->getStateUsing(function ($record) {
                        return $record->details->map(function ($detail) {
                            return $detail->consortia->name
                                . ' (' . $detail->start_date . ' - ' . $detail->end_date . ')'
                                . ': ' . $detail->description
                                . ($detail->combinable ? ' - Combinable' : '');
                        })->implode('<br>');
                    })
                    ->html(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->tooltip('Edit Affiliation')
                    ->form($this->schemeForm())
                    ->modalHeading('Edit Affiliation')
                    ->fillForm(function ($record) {
                        return [
                            'product_id' => $record->product_id,
                            'affiliation_name' => $record->affiliation_name,
                            'combinable' => $record->combinable,
                            'non_combinable' => $record->non_combinable,
                            'details' => $record->details->map(function ($detail) {
                                return [
                                    'consortia_id' => $detail->consortia_id,
                                    'start_date' => $detail->start_date,
                                    'end_date' => $detail->end_date,
                                    'description' => $detail->description,
                                    'combinable' => $detail->combinable,
                                ];
                            })->toArray(),
                        ];
                    })
                    ->action(function ($data, $record) {
                        $record->update($data);
                        if ($record->details) {
                            foreach ($record->details as $detail) {
                                $detail->delete();
                            }
                        }
                        if (!isset($data['details'])) return;
                        foreach ($data['details'] as $detailData) {
                            $record->details()->create($detailData);
                        }
                    })
                    ->visible(fn () => Gate::allows('create', Product::class)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => Gate::allows('create', Product::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        if (!isset($data['combinable'])) $data['combinable'] = null;
                        $affiliation = ProductAffiliation::create($data);
                        if (!isset($data['details'])) return;
                        foreach ($data['details'] as $detail) {
                            $affiliation->details()->create($detail);
                        }
                    })
                    ->createAnother(false)
                    ->tooltip('Add New Affiliation')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', Product::class) && !ProductAffiliation::where('product_id', $this->productId)->exists())            ]);
    }

    public function render()
    {
        return view('livewire.products.product-affiliations-table');
    }
}
