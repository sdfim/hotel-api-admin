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

class CreatePricingRule extends Component implements HasForms
{
    use HasPricingRuleFields;
    use InteractsWithForms;

    public ?array $data = [];

    public bool $isSrCreator = false;

    public ?string $rateCode = null;

    public ?int $giataCodeProperty = null;

    public function mount(bool $isSrCreator = false, ?int $giataCodeProperty = null, ?string $rateCode = null): void
    {
        $this->form->fill();
        $this->isSrCreator = $isSrCreator;
        $this->giataCodeProperty = $giataCodeProperty;
        $this->rateCode = $rateCode;
        if ($this->giataCodeProperty) {
            $this->data['conditions'] = [
                [
                    'field' => 'property',
                    'compare' => '=',
                    'value_from' => $this->giataCodeProperty,
                ],
            ];
            if ($this->rateCode) {
                $this->data['conditions'][] = [
                    'field' => 'rate_code',
                    'compare' => '=',
                    'value_from' => $this->rateCode,
                ];
            }
        }
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
            ->schema($this->pricingRuleFields('create'))
            ->statePath('data')
            ->model(PricingRule::class);
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    public function create($data = null): RedirectResponse|Redirector
    {
        $data = $data ?? $this->form->getState();
        $name = $data['name'] ?? null;

        $propertyCode = collect($data['conditions'] ?? [])
            ->first(fn ($c) => $c['field'] === 'property')['value_from'] ?? null;

        if ($name && $propertyCode) 
        {
            $exists = PricingRule::where('name', $name)
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

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.name' => 'A rule with this name already exists for the same property.',
                ]);
            }
        }
        $data['is_sr_creator'] = $data['is_sr_creator'] ?? $this->isSrCreator;

        if ($data['is_exclude_action']) {
            $data['manipulable_price_type'] = 'exclude_action';
            $data['price_value_target'] = 'exclude_action';
            $data['price_value'] = 0.0;
            $data['price_value_type'] = 'exclude_action';
        }

        $conditions = $data['conditions'] ?? [];
        unset($data['conditions']);
        $record = PricingRule::create($data);

        $record->conditions()->createMany($conditions);

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('pricing-rules.edit', [
            'pricing_rule' => $record,
        ]);
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.create-pricing-rules');
    }
}
