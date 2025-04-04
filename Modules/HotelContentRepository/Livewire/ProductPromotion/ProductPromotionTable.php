<?php

namespace Modules\HotelContentRepository\Livewire\ProductPromotion;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\ProductPromotion\AddProductPromotion;
use Modules\HotelContentRepository\Actions\ProductPromotion\EditProductPromotion;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductPromotion;

class ProductPromotionTable extends Component implements HasForms, HasTable
{
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
        $this->title = 'Promotions for '.$product->name;
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
            $this->title .= ' - Rate Name: '.$rate->name;
        }
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),
            TextInput::make('promotion_name')
                ->label('Promotion Name')
                ->required(),
            TextInput::make('rate_code')
                ->label('Rate Code'),
            Textarea::make('description')
                ->label('Description'),
            Grid::make()
                ->schema([
                    DatePicker::make('validity_start')
                        ->label('Travel Start Date')
                        ->native(false)
                        ->required(),
                    DatePicker::make('validity_end')
                        ->label('Travel End Datee')
                        ->native(false),
                ]),
            Grid::make()
                ->schema([
                    TextInput::make('min_night_stay')
                        ->label('Min Night Stay')
                        ->numeric(),
                    TextInput::make('max_night_stay')
                        ->label('Max Night Stay')
                        ->numeric(),
                ]),
            Grid::make()
                ->schema([
                    DatePicker::make('booking_start')
                        ->label('Booking Start Date')
                        ->native(false)
                        ->required(),
                    DatePicker::make('booking_end')
                        ->label('Booking End Date')
                        ->native(false)
                        ->required(),
                ]),
            Textarea::make('terms_conditions')
                ->label('Terms & Conditions'),
            Textarea::make('exclusions')
                ->label('Exclusions'),
            Select::make('galleries')
                ->label('Galleries')
                ->multiple()
                ->relationship('galleries', 'gallery_name')
                ->searchable()
                ->native(false),
            Grid::make()
                ->schema([
                    Checkbox::make('not_refundable')
                        ->label('Not Refundable'),
                    Checkbox::make('package')
                        ->label('Package'),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductPromotion::query()
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
                TextColumn::make('promotion_name')->label('Promotion Name')->searchable()->wrap(),
                TextColumn::make('rate_code')->label('Rate Code')->searchable()->wrap(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap()
                    ->limit(250),
                TextColumn::make('validity_start')->label('Validity Start')->date()->sortable(),
                TextColumn::make('validity_end')->label('Validity End')->date()->sortable(),
                TextColumn::make('booking_start')->label('Booking Start')->date()->sortable(),
                TextColumn::make('booking_end')->label('Booking End')->date()->sortable(),
                IconColumn::make('not_refundable')->label('Not Refundable'),
                IconColumn::make('package')->label('Package'),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->tooltip('Edit Promotion')
                        ->form($this->schemeForm())
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['galleries'] = $record->galleries->pluck('id')->toArray();

                            return $data;
                        })
                        ->action(function (ProductPromotion $record, array $data) {
                            /** @var EditProductPromotion $editProductPromotion */
                            $editProductPromotion = app(EditProductPromotion::class);
                            $editProductPromotion->updateWithGalleries($record, $data);
                        })
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->modalHeading('Edit Promotion')
                        ->visible(fn () => Gate::allows('create', Product::class)),
                    DeleteAction::make()
                        ->visible(fn () => Gate::allows('create', Product::class)),
                ])->visible(fn (ProductPromotion $record): bool => ($this->productId && $this->rateId === $record->rate_id) || ($this->productId && ! $this->rateId)),

            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->action(function ($data) {
                        if ($this->productId) {
                            $data['product_id'] = $this->productId;
                        }
                        /** @var AddProductPromotion $addProductPromotion */
                        $addProductPromotion = app(AddProductPromotion::class);
                        $addProductPromotion->createWithGalleries($data);
                    })
                    ->createAnother(false)
                    ->tooltip('Add New Promotion')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', Product::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-promotion-table');
    }
}
