<?php

namespace App\Livewire\PricingRules;

use App\Models\ExpediaContent;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class CreatePricingRules extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount (): void
    {
        $this->form->fill();
    }

    public function form (Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Suppliers::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('property')
                    ->label('Property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => ExpediaContent::where('name', 'like', "%{$search}%")->limit(20)->pluck('name', 'property_id')->toArray())
                    ->required(),
                Select::make('destination')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => ExpediaContent::where('city', 'like', "%{$search}%")->limit(20)->pluck('city', 'giata_TTIcode')->toArray())
                    ->required(),
                DateTimePicker::make('travel_date')
                    ->required(),
                TextInput::make('days')
                    ->required()
                    ->numeric(),
                TextInput::make('nights')
                    ->required()
                    ->numeric(),
                TextInput::make('rate_code')
                    ->required()
                    ->maxLength(191),
                Select::make('room_type')
                    ->options(function (Get $get, Set $set): array {
                        // reset room_type each time especially if property field value was changed
                        $set('room_type', '');
                        $options = [];
                        if($get('property')) {
                            $rooms = ExpediaContent::where('property_id', $get('property'))->first(['rooms']);

                            if ($rooms) {
                                foreach ($rooms->rooms as $id => $room) {
                                    $options[$id] = $room['name'];
                                }
                            }
                        }
                        return $options;
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('total_guests')
                    ->required()
                    ->numeric(),
                TextInput::make('room_guests')
                    ->required()
                    ->numeric(),
                TextInput::make('number_rooms')
                    ->required()
                    ->numeric(),
                TextInput::make('meal_plan')
                    ->required()
                    ->maxLength(191),
                TextInput::make('rating')
                    ->required()
                    ->maxLength(191),
                Select::make('price_type_to_apply')
                    ->required()
                    ->options([
                        'total_price' => 'Total Price',
                        'net_price' => 'Net Price',
                        'rate_price' => 'Rate Price',
                    ]),
                Select::make('price_value_type_to_apply')
                    ->required()
                    ->options([
                        'fixed_value' => 'Fixed Value',
                        'percentage,' => 'Percentage',
                    ])
                    ->live(),
                TextInput::make('price_value_to_apply')
                    ->required()
                    ->numeric(),
                Select::make('price_type_to_apply')
                    ->required()
                    ->options([
                        'guest' => 'Guest',
                        'per_room' => 'Per Room',
                        'per_night' => 'Per Night',
                    ])
                    ->visible(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
                    ->required(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
            ])
            ->statePath('data')
            ->model(PricingRules::class);
    }

    public function create (): void
    {
        $data = $this->form->getState();

        $record = PricingRules::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();
    }

    public function render (): View
    {
        return view('livewire.pricing-rules.create-pricing-rules');
    }
}
