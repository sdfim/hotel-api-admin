<?php

namespace Modules\HotelContentRepository\Livewire\PdGrid;

use Illuminate\Database\Eloquent\Collection;
use Modules\Enums\ContactInformationDepartmentEnum;
use Modules\HotelContentRepository\Models\Hotel;

trait PdGridTrait
{
    protected function getFormattedFeeTaxes(Hotel $record, string $type): string
    {
        return $record->product?->feeTaxes
            ->filter(fn ($feeTax) => $feeTax->type === $type)
            ->map(fn ($feeTax) => ($feeTax && $feeTax->name && $feeTax->apply_type)
                ? "{$feeTax->name} {$feeTax->net_value} ".($feeTax->value_type === 'Percentage' ? '%' : '$')." {$feeTax->apply_type->name}"
                : ''
            )
            ->filter()
            ->join(', ');
    }

    public function getPropertyNotes(Hotel $record): string
    {
        foreach ($record->product->descriptiveContentsSection as $section) {
            if ($section->descriptiveType->name === 'Note' || $section->descriptiveType->name === 'Notes') {
                return $section->value;
            }
        }

        return '';
    }

    public function getMealPlansAvailable(Hotel $record, string $type): string
    {
        return $record->hotel_board_basis === $type ? 'Y' : 'N';
    }

    public function getInclusions(Hotel $record): string
    {
        $str = [];
        $attributes = $record->product->attributes;
        foreach ($attributes as $attribute) {
            $str[] = $attribute->attribute->name;
        }

        return implode('; ', $str);
    }

    public function getConsortia(Hotel $record, string $type): string
    {
        $str = [];
        $affiliations = $record->product?->affiliations;
        foreach ($affiliations as $affiliation) {
            if ($affiliation->consortia->name !== $type) {
                continue;
            }
            $str[] = $affiliation->consortia->name
                .' ('.$affiliation->start_date.' - '.$affiliation->end_date.')'
                .': '.$affiliation->description
                .($affiliation->combinable ? ' - Combinable' : '');
        }

        return implode('; ', $str);
    }

    public function getConsortiaExit(Hotel $record, string $type): string
    {
        $affiliations = $record->product?->affiliations;
        foreach ($affiliations as $affiliation) {
            if ($affiliation->consortia->name === $type) {
                return 'Y';
            }
        }

        return 'N';
    }

    public function getDepositInformation(Hotel $record): string
    {
        if (! $record->product->depositInformations) {
            return '';
        }

        return $record->product->depositInformations->map(function ($depositInfo) {
            return "{$depositInfo->name},
                {$depositInfo->start_date},
                {$depositInfo->expiration_date},
                {$depositInfo->manipulable_price_type},
                {$depositInfo->price_value},
                {$depositInfo->price_value_type},
                {$depositInfo->price_value_target}, ".$this->formatConditions($depositInfo->conditions);
        })->implode('; ');
    }

    public function getCancellationPolicy(Hotel $record): string
    {
        if (! $record->product->cancellationPolicies) {
            return '';
        }

        return $record->product->cancellationPolicies->map(function ($policy) {
            return "{$policy->name},
                {$policy->start_date},
                {$policy->end_date},
                {$policy->manipulable_price_type},
                {$policy->price_value},
                {$policy->price_value_type},
                {$policy->price_value_target}, ".$this->formatConditions($policy->conditions);
        })->implode('; ');
    }

    private function formatConditions(Collection $conditions): string
    {
        return collect($conditions)->map(function ($condition) {
            $value = $condition['value'] ?? '';
            $valueFrom = $condition['value_from'] ?? '';
            $valueTo = $condition['value_to'] ?? '';

            return preg_replace(
                ['/ {2,}/', '/\s+([,.!?])/', '/\s+$/'],
                [' ', '$1', ''],
                "{$condition['field']} {$condition['compare']} {$value} {$valueFrom} {$valueTo}"
            );
        })->implode(', ');
    }

    public function getContactInformationEmail(Hotel $record, string $type): string
    {
        $contacts = $record->product->contactInformation;
        $emails = [];
        foreach ($contacts as $contact) {
            foreach ($contact->emails as $email) {
                foreach ($email->contactInformations ?? [] as $contactInformation) {
                    if ($contactInformation->name === $type) {
                        $emails[] = $email->email;
                    }
                    if (! in_array($contactInformation->name, [
                        ContactInformationDepartmentEnum::RESERVATION->value,
                        ContactInformationDepartmentEnum::CONCIERGE->value,
                        ContactInformationDepartmentEnum::SALES_MARKETING->value,
                    ]) && $type === 'All') {
                        $emails[] = $contactInformation->name.': '.$email->email;
                    }
                }
            }
        }

        return implode('; ', $emails);
    }
}
