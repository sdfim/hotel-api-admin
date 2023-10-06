<?php

namespace App\Livewire\GeneralConfiguration;

use App\Models\GeneralConfiguration;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Features\SupportRedirects\Redirector;

class CreateGeneralConfigurationForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public bool $create = true;
    public GeneralConfiguration $record;
    public function getDynamicModel()
    {
        if ($this->create) {
            return $this->record;
        } else {
            return GeneralConfiguration::class;
        }
    }
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('time_supplier_requests')
                    ->label('Time out on supplier requests')
                    ->minValue(0)
                    ->maxValue(999999999)
                    ->required()
                    ->numeric(),
                TextInput::make('time_reservations_kept')
                    ->label('Length of Time Reservations are kept are offloading')
                    ->minValue(0)
                    ->maxValue(999999999)
                    ->required()
                    ->numeric(),
                TextInput::make('currently_suppliers')
                    ->label('Which Suppliers are currently being searched for')
                    ->minLength(2)
                    ->maxLength(191)
                    ->required(),
                TextInput::make('time_inspector_retained')
                    ->label('How Long Inspector Data is retained')
                    ->minValue(0)
                    ->maxValue(999999999)
                    ->required()
                    ->numeric(),
                DateTimePicker::make('star_ratings')
                    ->label('What star ratings to be searched for on the system')
                    ->default('2019-08-19T13:45:00')
                    ->required(),
                DateTimePicker::make('stop_bookings')
                    ->label('Stop bookings with in a number of days / hours from time of search execution')
                    ->default('2019-08-19T13:45:00')
                    ->required(),
            ])

            ->statePath('data')
            ->model($this->getDynamicModel());
    }

    public function save(): Redirector|RedirectResponse
    {
        $request = (object)$this->form->getState();
        $general_configuration = GeneralConfiguration::get();

        if (count($general_configuration) == 0) {
            $general_configuration_row = new GeneralConfiguration();
            $general_configuration_row->time_supplier_requests = $request->time_supplier_requests;
            $general_configuration_row->time_reservations_kept = $request->time_reservations_kept;
            $general_configuration_row->currently_suppliers = $request->currently_suppliers;
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

    public function render(): View
    {
        return view('livewire.general-configuration.create-general-configuration-form');
    }
}
