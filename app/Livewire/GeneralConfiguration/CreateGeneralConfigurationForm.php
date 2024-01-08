<?php

namespace App\Livewire\GeneralConfiguration;

use App\Models\GeneralConfiguration;
use App\Models\Supplier;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class CreateGeneralConfigurationForm extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];
    /**
     * @var bool
     */
    public bool $create = true;
    /**
     * @var GeneralConfiguration
     */
    public GeneralConfiguration $record;

    /**
     * @return GeneralConfiguration|string
     */
    public function getDynamicModel(): string|GeneralConfiguration
    {
        if ($this->create) {
            return $this->record;
        } else {
            return GeneralConfiguration::class;
        }
    }

    /**
     * @param GeneralConfiguration|null $general_configuration
     * @return void
     */
    public function mount(?GeneralConfiguration $general_configuration): void
    {
        if (!empty($general_configuration->toArray())) {
            $this->record = $general_configuration;
            $this->form->fill($this->record->attributesToArray());
        } else {
            $this->create = false;
            $this->form->fill();
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 1,
                'xl' => 2,
                '2xl' => 2,
            ])
            ->schema([
                TextInput::make('time_supplier_requests')
                    ->label('Supplier requests timeout, seconds')
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(120)
                    ->required(),
                Select::make('currently_suppliers')
                    ->label('Include these suppliers in the search (PricingApi)')
                    ->multiple()
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('time_reservations_kept')
                    ->label('Length of Time Reservations are kept are offloading, days')
                    ->numeric()
                    ->minValue(7)
                    ->maxValue(365)
                    ->required(),
                Select::make('content_supplier')
                    ->label('Include this supplier in your search as a content supplier (ContentApi)')
                    ->options(['Expedia' => 'Expedia', 'IcePortal' => 'IcePortal', 'Expedia, IcePortal' => 'Expedia, IcePortal'])
                    ->required(),
                TextInput::make('time_inspector_retained')
                    ->label('How Long Inspector Data is retained, days')
                    ->numeric()
                    ->minValue(60)
                    ->maxValue(365)
                    ->required(),
                TextInput::make('star_ratings')
                    ->label('What star ratings to be searched for on the system, 0 ... 5.5')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0.0)
                    ->maxValue(5.5)
                    ->required(),
                TextInput::make('stop_bookings')
                    ->label('Stop bookings with in a number of hours from time of search execution, hours')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(365 * 24)
                    ->required(),
            ])
            ->statePath('data')
            ->model($this->getDynamicModel());
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function save(): Redirector|RedirectResponse
    {
        $request = (object)$this->form->getState();
        $general_configuration = GeneralConfiguration::get();

        if (count($general_configuration) === 0) {
            $general_configuration_row = new GeneralConfiguration();
            $general_configuration_row->time_supplier_requests = $request->time_supplier_requests;
            $general_configuration_row->time_reservations_kept = $request->time_reservations_kept;
            $general_configuration_row->currently_suppliers = $request->currently_suppliers;
            $general_configuration_row->content_supplier = $request->content_supplier;
            $general_configuration_row->time_inspector_retained = $request->time_inspector_retained;
            $general_configuration_row->star_ratings = $request->star_ratings;
            $general_configuration_row->stop_bookings = $request->stop_bookings;
            $general_configuration_row->save();
            Notification::make()
                ->title('Created successfully')
                ->success()
                ->send();
        } else {
            $general_configuration[0]->time_supplier_requests = $request->time_supplier_requests;
            $general_configuration[0]->time_reservations_kept = $request->time_reservations_kept;
            $general_configuration[0]->currently_suppliers = $request->currently_suppliers;
            $general_configuration[0]->content_supplier = $request->content_supplier;
            $general_configuration[0]->time_inspector_retained = $request->time_inspector_retained;
            $general_configuration[0]->star_ratings = $request->star_ratings;
            $general_configuration[0]->stop_bookings = $request->stop_bookings;
            $general_configuration[0]->save();
            Notification::make()
                ->title('Updated successfully')
                ->success()
                ->send();
        }
        return redirect()->route('general_configuration');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.general-configuration.create-general-configuration-form');
    }
}
