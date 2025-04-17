<?php

namespace App\Livewire\Configurations\RoomBedTypes;

use App\Models\Configurations\ConfigRoomBedType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class RoomBedTypeForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigRoomBedType $record;

    public function mount(ConfigRoomBedType $configRoomBedType): void
    {
        $this->record = $configRoomBedType;

        $this->form->fill($this->record->attributesToArray());
    }

    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->unique()
                ->required()
                ->maxLength(191),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema())
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.room-bed-types.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.room_bed_types.room-bed-type-form');
    }
}
