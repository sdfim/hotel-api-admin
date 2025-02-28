<?php

namespace Modules\HotelContentRepository\Livewire\TravelAgencyCommission;

use App\Livewire\Configurations\Consortia\ConsortiaForm;
use App\Models\Configurations\ConfigConsortium;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\CommissionValueTypeEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public int $productId;

    public string $title;

    public function mount(Product $product)
    {
        $this->productId = $product->id;
        $this->title = 'Travel Agency Commission for <h4>'.$product->name.'</h4>';
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),
            TextInput::make('name')
                ->label('Commission Name')
                ->required(),
            Grid::make()->schema([
                TextInput::make('commission_value')
                    ->label('Commission Value')
                    ->numeric('decimal')
                    ->required(),
                Select::make('commission_value_type')
                    ->label('Commission Value Type')
                    ->options(array_column(CommissionValueTypeEnum::cases(), 'value', 'value'))
                    ->required(),
            ]),
            Grid::make()->schema([
                DatePicker::make('date_range_start')
                    ->label('Travel Start Date')
                    ->native(false)
                    ->default(fn () => now())
                    ->required(),
                DatePicker::make('date_range_end')
                    ->label('Travel End Date')
                    ->native(false),
            ]),

            Grid::make()->schema([
                TagsInput::make('room_type')
                    ->label('Room Type')
                    ->separator('; '),
                Select::make('consortia')
                    ->label('Consortia')
                    ->multiple()
                    ->options(ConfigConsortium::pluck('name', 'id'))
                    ->createOptionForm(ConsortiaForm::getSchema())
                    ->createOptionUsing(function (array $data) {
                        $consortia = ConfigConsortium::create($data);
                        Notification::make()
                            ->title('Consortia created successfully')
                            ->success()
                            ->send();

                        return $consortia->id;
                    }),
            ]),

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(
                TravelAgencyCommission::query()->where('product_id', $this->productId)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Commission Name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('room_type')
                    ->label('Room Type')
                    ->searchable(),
                TextColumn::make('consortia')
                    ->label('Consortia')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $consortiaIds = explode(',', str_replace(' ', '', $state));
                        if (empty($consortiaIds)) {
                            return '';
                        }

                        return ConfigConsortium::whereIn('id', $consortiaIds)->pluck('name')->implode(', ');
                    }),
                TextColumn::make('commission_value')
                    ->label('Value')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('commission_value_type')
                    ->label('Value Type')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date_range_start')
                    ->label('Travel Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('date_range_end')
                    ->label('Travel End Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    public function render(): View
    {
        return view('livewire.commissions.travel-agency-commission-table');
    }
}
