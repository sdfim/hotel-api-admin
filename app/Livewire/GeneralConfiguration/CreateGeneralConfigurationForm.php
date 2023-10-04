<?php

namespace App\Livewire\GeneralConfiguration;

use App\Models\GeneralConfiguration;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class CreateGeneralConfigurationForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('time_supplier_requests')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('time_reservations_kept')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currently_suppliers')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('time_inspector_retained')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('star_ratings')
                    ->required(),
                Forms\Components\DateTimePicker::make('stop_bookings')
                    ->required(),
            ])
            ->statePath('data')
            ->model(GeneralConfiguration::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = GeneralConfiguration::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.general-configuration.create-general-configuration-form');
    }
}