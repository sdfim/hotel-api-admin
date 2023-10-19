<?php

namespace App\Livewire\PricingRules;

use Livewire\Component;
use App\Models\Channels;
use App\Models\GiataProperty;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportRedirects\Redirector;

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
                    ->required(),
                Select::make('channel_id')
                    ->label('Channel')
                    ->options(Channels::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('name', 'like', "%{$search}%")->limit(30)->pluck('full_name', 'code')->toArray()
                    )
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('destination', null);
                        $destination = GiataProperty::select('city')->where('code', $get('property'))->first();
                        $set('destination', $destination->city ?? '');
                    })
                    ->live()
                    ->required()
                    ->unique(),
                TextInput::make('destination')
                    ->readOnly()
                    ->required(),
                DateTimePicker::make('travel_date')
                    ->required()
                    ->default(now()),
                DateTimePicker::make('rule_start_date')
                    ->required()
                    ->default(now()),
                DateTimePicker::make('rule_expiration_date')
                    ->required()
                    ->default(now()),
                TextInput::make('days')
                    ->required()
                    ->numeric(),
                TextInput::make('nights')
                    ->required()
                    ->numeric(),
                TextInput::make('rate_code')
                    ->required()
                    ->maxLength(191),
                TextInput::make('room_type')
                    ->required()
                    ->maxLength(191),
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
                Select::make('price_value_type_to_apply')
                    ->options([
                        'fixed_value' => 'Fixed Value',
                        'percentage' => 'Percentage',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('price_value_to_apply')
                    ->numeric()
                    ->required(),
                Select::make('price_type_to_apply')
                    ->options([
                        'total_price' => 'Total Price',
                        'net_price' => 'Net Price',
                        'rate_price' => 'Rate Price',
                    ])
                    ->required(),
                Select::make('price_value_fixed_type_to_apply')
                    ->options([
                        'per_guest' => 'Per Guest',
                        'per_room' => 'Per Room',
                        'per_night' => 'Per Night',
                    ])
                    ->visible(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
                    ->required(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
            ])
            ->statePath('data')
            ->model(PricingRules::class);
    }

    protected function onValidationError (ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    public function create (): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $record = PricingRules::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('pricing_rules.index');
    }

    public function render (): View
    {
        return view('livewire.pricing-rules.create-pricing-rules');
    }
}
