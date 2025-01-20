<?php

namespace App\Livewire\Configurations\Amenities;

use App\Models\Configurations\ConfigAmenity;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class AmenitiesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigAmenity $record;

    public function mount(ConfigAmenity $configAmenity): void
    {
        $this->record = $configAmenity;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema())
            ->statePath('data')
            ->model($this->record);
    }

    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
            ->required()
            ->maxLength(191)
            ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        if (!isset($data['default_value'])) {
            $data['default_value'] = '';
        }
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.amenities.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.amenities.amenities-form');
    }
}
