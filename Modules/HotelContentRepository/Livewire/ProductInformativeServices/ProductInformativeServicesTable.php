<?php

namespace Modules\HotelContentRepository\Livewire\ProductInformativeServices;

use App\Helpers\ClassHelper;
use App\Livewire\Configurations\ServiceTypes\ServiceTypesForm;
use App\Models\Configurations\ConfigServiceType;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\ProductInformativeService\AddProductInformativeService;
use Modules\HotelContentRepository\Actions\ProductInformativeService\EditProductInformativeService;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServicesTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public ?int $rateId = null;

    public ?int $roomId = null;

    public string $title;

    public function mount(Product $product, ?int $rateId = null, ?int $roomId = null)
    {
        $this->productId = $product->id;
        $this->rateId = $rateId;
        $this->roomId = $roomId;
        $this->title = 'Informational Service for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            Hidden::make('rate_id')->default($this->rateId),
            Hidden::make('room_id')->default($this->roomId),

            Grid::make(3)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->columnSpan(2),
                    Select::make('service_id')
                        ->label('Service Type')
                        ->options(ConfigServiceType::all()->pluck('name', 'id')->toArray())
                        ->createOptionForm(ServiceTypesForm::getSchema())
                        ->createOptionUsing(function (array $data) {
                            ConfigServiceType::create($data);
                            Notification::make()
                                ->title('Service created successfully')
                                ->success()
                                ->send();
                        })
                        ->required(),
                    TextInput::make('cost')
                        ->label('Total Rack')
                        ->numeric()
                        ->required(),
                    Select::make('currency')
                        ->label('Currency')
                        ->required()
                        ->options([
                            'USD' => 'USD',
                            'EUR' => 'EUR',
                            'GBP' => 'GBP',
                            'JPY' => 'JPY',
                            'AUD' => 'AUD',
                            'CAD' => 'CAD',
                            'CHF' => 'CHF',
                            'CNY' => 'CNY',
                            'SEK' => 'SEK',
                            'NZD' => 'NZD',
                        ]),
                    TimePicker::make('service_time')
                        ->label('Service Time')
//                        ->native(false)
                        ->required()
                        ->format('h:i A'),
                ]),

            Grid::make(4)
                ->schema([
                    Checkbox::make('show_service_on_pdf')
                        ->label('Show Service on PDF'),
                    Checkbox::make('show_service_data_on_pdf')
                        ->label('Show Service Data on PDF'),
                    Checkbox::make('auto_book')
                        ->label('Auto Book'),
                    Checkbox::make('commissionable')
                        ->label('Commissionable'),
                ]),

            CustomRepeater::make('dynamicColumns')
                ->label('Dynamic Columns')
                ->schema([
                    TextInput::make('name')
                        ->hiddenLabel()
                        ->placeholder('Name')
                        ->required(),
                    Textarea::make('value')
                        ->hiddenLabel()
                        ->placeholder('Value')
                        ->required(),
                ])
                ->columns(2),

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductInformativeService::with('service')->where('product_id', $this->productId)
                    ->where('rate_id', $this->rateId)
                    ->where('room_id', $this->roomId))
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('service.name')->label('Service Type')->searchable(),
                TextColumn::make('cost')->label('Total Rack')->searchable(),
                TextColumn::make('currency')->label('Currency')->searchable(),
                TextColumn::make('service_time')->label('Service Time')->searchable(),
                IconColumn::make('show_service_on_pdf')->label('Show on PDF')->boolean(),
                IconColumn::make('show_service_data_on_pdf')->label('Show Data on PDF')->boolean(),
                IconColumn::make('commissionable')->label('Commissionable')->boolean(),
                IconColumn::make('auto_book')->label('Auto Book')->boolean(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->tooltip('Edit Service')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['dynamicColumns'] = $record->dynamicColumns->toArray();

                        return $data;
                    })
                    ->action(function ($data, $record) {
                        /** @var EditProductInformativeService $editProductInformativeService */
                        $editProductInformativeService = app(EditProductInformativeService::class);
                        $editProductInformativeService->updateWithDynamicColumns($record, $data);
                        Notification::make()
                            ->title('Service updated successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => Gate::allows('create', Product::class)),
            ])
            ->bulkActions($this->getBulkActions())
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->iconButton()
                    ->tooltip('Add New Service')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->form($this->schemeForm())
                    ->createAnother(false)
                    ->action(function ($data) {
                        /** @var AddProductInformativeService $addProductInformativeService */
                        $addProductInformativeService = app(AddProductInformativeService::class);
                        $addProductInformativeService->createWithDynamicColumns($data);
                        Notification::make()
                            ->title('Service created successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.products.product-informative-services-table');
    }
}
