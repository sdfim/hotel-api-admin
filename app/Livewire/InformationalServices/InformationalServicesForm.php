<?php

namespace App\Livewire\InformationalServices;

use App\Models\InformationalService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class InformationalServicesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public InformationalService $record;

    public function mount(InformationalService $service): void
    {
        $this->record = $service;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->maxLength(191),
                Textarea::make('description')->nullable(),
                TextInput::make('cost')->numeric()->required(),
                Grid::make()
                    ->schema([
                        DatePicker::make('date')->native(false)->required(),
                        TimePicker::make('time')->native(false)->required(),
                    ]),
                Select::make('type')->options([
                    'type1' => 'Type 1',
                    'type2' => 'Type 2',
                ])->required(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $exists = $this->record->exists;
        $data = $this->form->getState();
        $this->record->fill(Arr::only($data, ['name', 'description', 'cost', 'date', 'time', 'type']));

        $this->record->save();

        Notification::make()
            ->title($exists ? 'Updated successfully' : 'Created successfully')
            ->success()
            ->send();

        return redirect()->route('informational-services.index');
    }

    public function render(): View
    {
        return view('livewire.informational-services.informational-services-form');
    }
}
