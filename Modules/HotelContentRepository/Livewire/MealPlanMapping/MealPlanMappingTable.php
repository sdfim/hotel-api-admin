<?php

namespace Modules\HotelContentRepository\Livewire\MealPlanMapping;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Modules\Enums\MealPlansEnum;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\MealPlanMapping;

class MealPlanMappingTable extends Component implements HasForms, HasTable
{
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Hotel $hotel = null;

    public ?int $giataCode = null;

    public string $title = 'Meal Plan Mapping';

    /**
     * Initialize component with hotel record.
     */
    public function mount(Hotel $hotel): void
    {
        // Assign hotel instance for later use
        $this->hotel = $hotel;

        // Pre-fill giata_code if needed
        $this->giataCode = $hotel->giata_code;

        $this->title = 'Meal Plan Mapping for <h4>' . $hotel->product->name . '</h4>';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => MealPlanMapping::query()
                    ->where('giata_id', $this->hotel->giata_code)
            )
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('meal_plan_code_from_supplier')
                    ->label('Meal Plan Code')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('rate_plan_code_from_supplier')
                    ->label('Rate Plan Code')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('our_meal_plan')
                    ->label('Our Meal Plan')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
            ])
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->headerActions($this->getHeaderActions());
    }

    /**
     * Shared Filament form schema for both Create/Edit.
     * giata_id is always hidden and automatically assigned.
     */
    protected function schemeForm(): array
    {
        return [
            Hidden::make('giata_id')
                ->default($this->hotel->giata_code),

            Grid::make(2)->schema([
                TextInput::make('rate_plan_code_from_supplier')
                    ->label('Rate Plan Code From Supplier')
                    ->helperText('Optional. Supplier may encode meal plan through rate plan code.')
                    ->maxLength(191),

                TextInput::make('meal_plan_code_from_supplier')
                    ->label('Meal Plan Code From Supplier')
                    ->helperText('Optional. Raw meal plan code from supplier.')
                    ->maxLength(191),
            ]),

            Select::make('our_meal_plan')
                ->label('Our Meal Plan')
                ->required()
                ->options(array_combine(
                    MealPlansEnum::values(),
                    MealPlansEnum::values()
                ))
                ->helperText('This value will be used in API and emails.'),

            Checkbox::make('is_enabled')
                ->label('Enabled')
                ->default(true),
        ];
    }

    public function render()
    {
        return view('livewire.meal-plan-mapping.meal-plan-mapping-table');
    }
}
