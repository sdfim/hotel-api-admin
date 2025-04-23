<?php

namespace App\Livewire\Configurations\InsuranceDocumentationTypes;

use App\Models\Configurations\ConfigInsuranceDocumentationType;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;

class InsuranceDocumentationTypesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigInsuranceDocumentationType $record;

    public function mount(ConfigInsuranceDocumentationType $configInsuranceDocumentationType): void
    {
        $this->record = $configInsuranceDocumentationType;
        $data = $this->record->attributesToArray();

        // Initialize toggle states based on the viewable array
        $data['viewable_external'] = in_array('External', $data['viewable'] ?? []);
        $data['viewable_internal'] = in_array('Internal', $data['viewable'] ?? []);

        if (! $this->record->exists) {
            $data['viewable_internal'] = true;
        }

        $this->form->fill($data);
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
            TextInput::make('name_type')
                ->label('Name Type')
                ->required()
                ->maxLength(191),

            Fieldset::make('Viewable')
                ->columns(6)
                ->schema([
                    CustomToggle::make('viewable_internal')
                        ->label('Internal')
                        ->inline(true)
                        ->default(true),

                    CustomToggle::make('viewable_external')
                        ->label('External')
                        ->inline(true),
                ]),
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $viewable = [];
        if ($data['viewable_external']) {
            $viewable[] = 'External';
        }
        if ($data['viewable_internal']) {
            $viewable[] = 'Internal';
        }
        $data['viewable'] = $viewable;

        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.insurance-documentation-types.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.insurance-documentation-types.insurance-documentation-types-form');
    }
}
