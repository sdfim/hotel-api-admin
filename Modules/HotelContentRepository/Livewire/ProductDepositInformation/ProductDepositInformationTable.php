<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\ClassHelper;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\DaysPriorTypeEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasProductActions;
    use DepositFieldTrait;

    public int $productId;
    public string $title;

    public function mount(int $productId)
    {
        $this->productId = $productId;
        $product = Product::find($productId);
        $this->title = 'Deposit Information for <h4>' . ($product ? $product->name : 'Unknown Hotel') . '</h4>';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductDepositInformation::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('start_date')->label('Start Date')->date()->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Expiration Date')
                    ->date()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $date = Carbon::parse($state)->format('M j, Y');
                        return $date === 'Feb 2, 2112' ? '' : $date;
                    }),
                TextColumn::make('manipulable_price_type')
                    ->label('Price Type')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('price_value_type')
                    ->label('Value Type')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('price_value')->label('Value')->searchable(),
                TextColumn::make('price_value_target')
                    ->label('Value Target')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->tooltip('Edit Deposit Information')
                    ->form(fn ($record) => $this->schemeForm($record))
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['conditions'] = $record->conditions->toArray();
                        return $data;
                    })
                    ->action(function (array $data, ProductDepositInformation $record) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        if (!$data['expiration_date']) $data['expiration_date'] = Carbon::create(2112, 02, 02);

                        $record->update($data);

                        if (isset($data['conditions'])) {
                            foreach ($data['conditions'] as $condition) {
                                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                                    $condition['value_from'] = null;
                                } else {
                                    $condition['value'] = null;
                                }
                                if (isset($condition['id'])) {
                                    $record->conditions()->updateOrCreate(['id' => $condition['id']], $condition);
                                } else {
                                    $record->conditions()->create($condition);
                                }
                            }
                        }
                    })
                    ->modalWidth('7xl')
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
                    ->modalWidth('7xl')
                    ->createAnother(false)
                    ->action(function ($data) {
                        if ($this->productId) $data['product_id'] = $this->productId;
                        if (!$data['expiration_date']) $data['expiration_date'] = Carbon::create(2112, 02, 02);
                        $productDepositInformation = ProductDepositInformation::create($data);
                        if (isset($data['conditions'])) {
                            foreach ($data['conditions'] as $condition) {
                                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                                    $condition['value_from'] = null;
                                } else {
                                    $condition['value'] = null;
                                }
                                $productDepositInformation->conditions()->create($condition);
                            }
                        }
                    })
                    ->tooltip('Add New Deposit Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', Product::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-deposit-information-table');
    }
}
