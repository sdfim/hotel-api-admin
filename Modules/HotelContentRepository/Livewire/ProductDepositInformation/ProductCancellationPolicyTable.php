<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\ClassHelper;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\ProductCancellationPolicy\AddProductCancellationPolicy;
use Modules\HotelContentRepository\Actions\ProductCancellationPolicy\EditProductCancellationPolicy;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class ProductCancellationPolicyTable extends Component implements HasForms, HasTable
{
    use DepositFieldTrait;
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $rate = HotelRate::where('id', $rateId)->first();
        $this->title = 'Cancellation Policy for '.$product->name;
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
            $this->title .= ' - Rate Name: '.$rate->name;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductCancellationPolicy::query()
                    ->where('product_id', $this->productId)
            )
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->rateId) {
                    $query->where(function ($q) {
                        $q->where('rate_id', $this->rateId)
                            ->orWhereNull('rate_id');
                    });
                } else {
                    $query->whereNull('rate_id');
                }
            })
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return ($this->productId && $this->rateId && $this->rateId === $record->rate_id) ? 'Rate' : 'Hotel';
                    })
                    ->colors([
                        'primary' => 'Hotel',
                        'warning' => 'Rate',
                    ]),
                TextColumn::make('name')->label('Name')->wrap()->searchable(),
                TextColumn::make('start_date')->label('Travel Start Date')->date()->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Travel End Date')
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
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
            ])
            ->deferLoading()
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form(fn ($record) => $this->schemeForm($record))
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['conditions'] = $record->conditions->toArray();

                            return $data;
                        })
                        ->action(function (array $data, ProductCancellationPolicy $record) {
                            if ($this->productId) {
                                $data['product_id'] = $this->productId;
                            }
                            if (! $data['expiration_date']) {
                                $data['expiration_date'] = Carbon::create(2112, 02, 02);
                            }
                            /** @var EditProductCancellationPolicy $editProductCancellationPolicy */
                            $editProductCancellationPolicy = app(EditProductCancellationPolicy::class);
                            $editProductCancellationPolicy->updateWithConditions($record, $data);
                        })
                        ->modalWidth('7xl')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductCancellationPolicy $record): bool => ($this->productId && $this->rateId === $record->rate_id) || ($this->productId && ! $this->rateId)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm())
                    ->modalWidth('7xl')
                    ->createAnother(false)
                    ->action(function ($data) {
                        if ($this->productId) {
                            $data['product_id'] = $this->productId;
                        }
                        if (! $data['expiration_date']) {
                            $data['expiration_date'] = Carbon::create(2112, 02, 02);
                        }
                        /** @var AddProductCancellationPolicy $addProductCancellationPolicy */
                        $addProductCancellationPolicy = app(AddProductCancellationPolicy::class);
                        $addProductCancellationPolicy->createWithConditions($data);
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
        return view('livewire.products.product-cancellation-policy-table');
    }
}
