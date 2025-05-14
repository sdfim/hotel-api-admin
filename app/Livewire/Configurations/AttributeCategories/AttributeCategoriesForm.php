<?php

namespace App\Livewire\Configurations\AttributeCategories;

use App\Models\Configurations\ConfigAttributeCategory;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class AttributeCategoriesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigAttributeCategory $record;

    public function mount(ConfigAttributeCategory $configAttributeCategory): void
    {
        $this->record = $configAttributeCategory;
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
                ->maxLength(191),
        ];
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

        return redirect()->route('configurations.attribute-categories.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.attribute-categories.attribute-categories-form');
    }
}
