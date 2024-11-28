<?php

namespace Modules\Insurance\Livewire\Restrictions;

use App\Helpers\ClassHelper;
use App\Models\Property;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;

class RestrictionsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $restrictionTypes = [];
    public ?int $vendorId;
    public bool $viewAll = false;

    public function mount(?Vendor $vendor, bool $viewAll = false): void
    {
        $this->vendorId = $vendor->id;
        $this->viewAll = $viewAll;
        $this->restrictionTypes = InsuranceRestrictionType::pluck('id', 'name')->toArray();
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->relationship(name: 'vendor', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    Select::make('restriction_type_id')
                        ->label('Restriction Type')
                        ->relationship(name: 'restrictionType', titleAttribute: 'label')
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Select $component) => $component
                            ->getContainer()
                            ->getComponent('dynamicFieldValue')
                            ->getChildComponentContainer()
                            ->fill()
                        ),
                    Select::make('compare')
                        ->label('Compare')
                        ->options(fn(Get $get): array => match (array_search($get('restriction_type_id'), $this->restrictionTypes)) {
                            'customer_location', 'travel_location' => [
                                '!=' => '!=',
                            ],
                            'age' => [
                                '>' => '>',
                            ],
                            default => [
                                '=' => '=',
                                '<' => '<',
                                '>' => '>'
                            ],
                        })
                        ->required(),
                    Grid::make(1)
                        ->schema(fn(Get $get): array => match (array_search($get('restriction_type_id'), $this->restrictionTypes)) {
                            'age' => [
                                TextInput::make('value')
                                    ->label('Value')
                                    ->maxValue(21)
                                    ->integer()
                                    ->required(),
                            ],
                            'insurance_return_period_days', 'trip_duration_days' => [
                                TextInput::make('value')
                                    ->label('Value')
                                    ->integer()
                                    ->required(),
                            ],
                            'customer_location', 'travel_location' => [
                                Select::make('value')
                                    ->label('Value')
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        $result = Property::select(
                                            DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'), 'city_id')
                                            ->where('city', 'like', "%$search%")->limit(30);

                                        return $result->pluck('full_name', 'city_id')->toArray() ?? [];
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $result = Property::select(
                                            DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'))
                                            ->where('city_id', $value)->first();

                                        return $result->full_name ?? '';
                                    })
                                    ->required(),
                            ],
                            'trip_cost' => [
                                TextInput::make('value')
                                    ->label('Value')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->required(),
                            ],
                            default => [
                                TextInput::make('value')
                                    ->label('Value')
                                    ->disabled(),
                            ],
                        })
                        ->key('dynamicFieldValue')
                        ->columnStart(4)
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->viewAll
                ? InsuranceRestriction::query()
                : InsuranceRestriction::query()->where('vendor_id', $this->vendorId))
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('restrictionType.label')
                    ->label('Restriction type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('compare')
                    ->label('Compare sign')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('value')
                    ->label('Restriction value')
                    ->sortable()
                    ->formatStateUsing(function (Model $record) {
                        $restrictionType = $record->restrictionType->name;

                        if ($restrictionType === 'customer_location' || $restrictionType === 'travel_location') {
                            return Property::select(
                                DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'))
                                ->where('city_id', $record->value)->first()->full_name ?? $record->value;
                        }

                        return $record->value;
                    })
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Restriction')
                    ->form(fn() => $this->schemeForm())
                    ->fillForm(function (InsuranceRestriction $record) {
                        return $record->toArray();
                    })
                    ->action(function (InsuranceRestriction $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();

                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Restriction')
                    ->requiresConfirmation()
                    ->action(function (InsuranceRestriction $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->action(function (array $data) {
                        InsuranceRestriction::create($data);

                        Notification::make()
                            ->title('Created successfully')
                            ->success()
                            ->send();

                        return $data;
                    })
                    ->tooltip('Add New Restriction')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.restrictions.restrictions-table');
    }
}
