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

class CreateRestrictionForm extends Component implements HasForms
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
                //
            ])
            ->statePath('data')
            ->model(InsuranceRestriction::class);
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $record = InsuranceRestriction::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('insurance-restrictions.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.restrictions.create-restriction-form');
    }
}
