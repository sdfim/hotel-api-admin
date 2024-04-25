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
    use InteractsWithForms, HasPricingRuleFields;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @var PricingRule
     */
    public PricingRule $record;

    /**
     * @param PricingRule $pricingRule
     * @return void
     */
    public function mount(PricingRule $pricingRule): void
    {
        $this->record = $pricingRule;
        $this->form->fill($this->record->attributesToArray());
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 1,
                'md' => 2,
                'xl' => 3,
                '2xl' => 3,
            ])
            ->schema($this->pricingRuleFields())
            ->statePath('data')
            ->model($this->record);
    }

    /**
     * @param ValidationException $exception
     * @return void
     */
    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    /**
     * @return RedirectResponse|Redirector
     */
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

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
