<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\ClassHelper;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
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
use Modules\HotelContentRepository\Actions\ProductDepositInformation\AddProductDepositInformation;
use Modules\HotelContentRepository\Actions\ProductDepositInformation\EditProductDepositInformation;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationTable extends Component implements HasForms, HasTable
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
        $this->title = 'Deposit Information for '.$product->name;
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
            $this->title .= ' - Rate Name: '.$rate->name;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductDepositInformation::query()
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
            ->deferLoading()
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
                TextColumn::make('start_date')->label('Rule Start Date')->date()->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Rule End Date')
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
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->tooltip('Edit Deposit Information')
                        ->form(fn ($record) => $this->schemeForm($record, true))
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['conditions'] = $record->conditions->toArray();

                            return $data;
                        })
                        ->action(function (array $data, ProductDepositInformation $record) {
                            if ($this->productId) {
                                $data['product_id'] = $this->productId;
                            }
                            if (! $data['expiration_date']) {
                                $data['expiration_date'] = Carbon::create(2112, 02, 02);
                            }
                            /* @var EditProductDepositInformation $editProductDepositInformation */
                            $editProductDepositInformation = app(EditProductDepositInformation::class);
                            $editProductDepositInformation->updateWithConditions($record, $data);
                        })
                        ->modalWidth('7xl')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    Action::make('clone')
                        ->label('Clone')
                        ->color('success')
                        ->icon('heroicon-o-clipboard-document')
                        ->tooltip('Clone Deposit Information')
                        ->modalHeading(new HtmlString("Clone {$this->title}"))
                        ->modalWidth('7xl')
                        ->form(fn ($record) => $this->schemeForm($record, true))
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['conditions'] = $record->conditions->toArray();
                            $data['name'] = $data['name'].' (Clone - '.now()->format('Y-m-d H:i:s').')';

                            return $data;
                        })
                        ->action(function (array $data, ProductDepositInformation $record) {
                            if ($this->productId) {
                                $data['product_id'] = $this->productId;
                            }
                            if ($this->rateId) {
                                $data['rate_id'] = $this->rateId;
                            }
                            if (! $data['expiration_date']) {
                                $data['expiration_date'] = Carbon::create(2112, 02, 02);
                            }
                            /* @var AddProductDepositInformation $addProductDepositInformation */
                            $addProductDepositInformation = app(AddProductDepositInformation::class);
                            $addProductDepositInformation->createWithConditions($data);
                        })
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->tooltip('Delete Deposit Information')
                        ->action(function (ProductDepositInformation $record) {
                            $record->delete();
                        })
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductDepositInformation $record): bool => ($this->productId && $this->rateId === $record->rate_id) || ($this->productId && ! $this->rateId)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm(null, true))
                    ->modalWidth('7xl')
                    ->createAnother(false)
                    ->action(function ($data) {
                        if ($this->productId) {
                            $data['product_id'] = $this->productId;
                        }
                        if (! $data['expiration_date']) {
                            $data['expiration_date'] = Carbon::create(2112, 02, 02);
                        }
                        /* @var AddProductDepositInformation $addProductDepositInformation */
                        $addProductDepositInformation = app(AddProductDepositInformation::class);
                        $addProductDepositInformation->createWithConditions($data);
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
