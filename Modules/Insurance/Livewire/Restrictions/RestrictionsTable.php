<?php

namespace Modules\Insurance\Livewire\Restrictions;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\InsuranceRestrictionSaleTypeEnum;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;

class RestrictionsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $restrictionTypes = [];

    public function mount(): void
    {
        $this->restrictionTypes = InsuranceRestrictionType::pluck('id', 'name')->toArray();
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('vendor_id')
                ->label('Vendor')
                ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                ->preload()
                ->required(),
            Grid::make(2)
                ->schema([
                    Select::make('restriction_type_id')
                        ->label('Restriction Type')
                        ->relationship(name: 'restrictionType', titleAttribute: 'label')
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Select $component) => $component
                            ->getContainer()
                            ->getComponent('dynamicFieldValue')
                            ->getChildComponentContainer()
                            ->fill()
                        ),
                    Select::make('sale_type')
                        ->label('Sale Type')
                        ->options(InsuranceRestrictionSaleTypeEnum::getOptions())
                        ->preload()
                        ->required(),
                    Select::make('compare')
                        ->label('Compare')
                        ->options(fn (Get $get): array => match (array_search($get('restriction_type_id'), $this->restrictionTypes)) {
                            'customer_location', 'travel_location' => [
                                '!=' => '!=',
                                '=' => '=',
                            ],
                            default => [
                                '=' => '=',
                                '<' => '<',
                                '>' => '>',
                                '>=' => '>=',
                                '<=' => '<=',
                            ],
                        })
                        ->required(),
                    Grid::make(1)
                        ->schema(fn (Get $get): array => match (array_search($get('restriction_type_id'), $this->restrictionTypes)) {
                            'age' => [
                                TextInput::make('value')
                                    ->label('Value')
//                                    ->maxValue(21)
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
                                            ->where('city', 'like', "%$search%")
                                            ->where('locale', 'like', "%$search%")
                                            ->limit(30);

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
                        ->columnStart(2),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsuranceRestriction::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id),
                    )
            )
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insuranceType.name')
                    ->label('Insurance type')
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
                    }),
                TextColumn::make('sale_type')
                    ->label('Sale Type')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Restriction')
                    ->form(fn () => $this->schemeForm())
                    ->fillForm(function (InsuranceRestriction $record) {
                        return $record->toArray();
                    })
                    ->visible(fn (InsuranceRestriction $record): bool => Gate::allows('update', $record))
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
                    ->visible(fn (InsuranceRestriction $record): bool => Gate::allows('delete', $record))
                    ->action(function (InsuranceRestriction $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->createAnother(false)
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
                    ->iconButton()
                    ->visible(fn (): bool => Gate::allows('create', InsuranceRestriction::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.restrictions.restrictions-table');
    }
}
