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
        $data = $this->record->attributesToArray();
        $data['conditions'] = $this->record->conditions->toArray();

        $this->form->fill($data);
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
        $name = $data['name'] ?? null;

        $propertyCode = collect($data['conditions'] ?? [])
            ->first(fn ($c) => $c['field'] === 'property')['value_from'] ?? null;

        if ($name && $propertyCode) 
        {
            $exists = PricingRule::where('name', $name)
                ->where('id', '!=', $this->record->id)
                ->whereHas('conditions', function ($q) use ($propertyCode) {
                    $q->where('field', 'property')
                    ->where('value_from', $propertyCode);
                })
                ->exists();

            if ($exists) 
            {
                Notification::make()
                    ->title('Validation error')
                    ->body('A rule with this name already exists for the same property.')
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    'data.name' => 'A rule with this name already exists for the same property.',
                ]);
            }
        }

        $this->record->update($data);

        $conditions = $data['conditions'] ?? [];
        $this->record->conditions()->delete();
        $this->record->conditions()->createMany($conditions);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('pricing-rules.edit', [
            'pricing_rule' => $this->record,
        ]);
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
