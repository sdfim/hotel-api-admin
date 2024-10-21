<?php

namespace App\Livewire\Insurance\Restrictions;

use App\Models\Property;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;

class CreateRestrictionForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public array $restrictionTypes = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->restrictionTypes = InsuranceRestrictionType::pluck('id', 'name')->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Select::make('insurance_plan_id')
                            ->label('Insurance Plan')
                            ->relationship(name: 'plan', titleAttribute: 'booking_item')
                            ->searchable(),
                        Select::make('provider_id')
                            ->label('Provider')
                            ->relationship(name: 'provider', titleAttribute: 'name')
                            ->preload()
                            ->required(),
                    ]),
                Grid::make(3)
                    ->schema([
                        Select::make('restriction_type_id')
                            ->label('Restriction Type')
                            ->relationship(name: 'restrictionType', titleAttribute: 'name')
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
                                    '=' => '=',
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
                                'age', 'insurance_return_period_days', 'trip_duration_days' => [
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
                            ->columnStart(3)
                    ]),
            ])
            ->statePath('data')
            ->model(InsuranceRestriction::class);
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $record = InsuranceRestriction::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('insurance-restrictions.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.restrictions.create-restriction-form');
    }
}
