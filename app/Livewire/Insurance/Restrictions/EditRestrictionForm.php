<?php

namespace App\Livewire\Insurance\Restrictions;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Insurance\Models\InsuranceRestriction;

class EditRestrictionForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public InsuranceRestriction $record;

    public function mount(InsuranceRestriction $insuranceRestriction): void
    {
        $this->record = $insuranceRestriction;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('insurance-restrictions.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.restrictions.edit-restriction-form');
    }
}
