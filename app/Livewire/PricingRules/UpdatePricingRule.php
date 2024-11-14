<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRule;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdatePricingRule extends Component implements HasForms
{
    use HasPricingRuleFields, InteractsWithForms;

    public ?array $data = [];

    public PricingRule $record;
    public bool $isSrCreator = false;


    public function mount(PricingRule $pricingRule): void
    {
        $this->record = $pricingRule;
        $this->isSrCreator = $this->record->is_sr_creator;
        $this->record->rule_start_date = optional($pricingRule->rule_start_date)->format('Y-m-d');
        $this->record->rule_expiration_date = optional($pricingRule->rule_expiration_date)->format('Y-m-d');
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 1,
                'md' => 2,
                'xl' => 3,
                '2xl' => 3,
            ])
            ->schema($this->pricingRuleFields('edit'))
            ->statePath('data')
            ->model($this->record);
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    public function edit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('pricing-rules.index');
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
