<?php

namespace Modules\Enums;

enum InsuranceDocTypeEnum: string
{
    case PRIVACY_POLICY = 'privacy_policy';
    case TERMS_AND_CONDITION = 'terms_and_condition';
    case TRAVEL_PROTECTION_PLAN_SUMMARY = 'travel_protection_plan_summary';
    case SCHEDULE_OF_BENEFITS_PLAN_COSTS = 'schedule_of_benefits_plan_costs';
    case CLAIM_PROCESS = 'claim_process';
    case EMERGENCY_TRAVEL_ASSISTANCE_PLATINUM = 'emergency_travel_assistance_platinum';
    case EMERGENCY_TRAVEL_ASSISTANCE_SILVER = 'emergency_travel_assistance_silver';
    case TRIPMATE_CLAIMS = 'tripmate_claims';
    case GENERAL_INFORMATION = 'general_information';

    public function label(): string
    {
        return match ($this) {
            self::PRIVACY_POLICY => 'Privacy Policy',
            self::TERMS_AND_CONDITION => 'Terms & Conditions',
            self::TRAVEL_PROTECTION_PLAN_SUMMARY => 'Travel Protection Plan Summary',
            self::SCHEDULE_OF_BENEFITS_PLAN_COSTS => 'Schedule of Benefits & Plan Costs',
            self::CLAIM_PROCESS => 'Claim Process',
            self::EMERGENCY_TRAVEL_ASSISTANCE_PLATINUM => 'Emergency Travel Assistance (Platinum Plan)',
            self::EMERGENCY_TRAVEL_ASSISTANCE_SILVER => 'Emergency Travel Assistance (Silver Plan)',
            self::TRIPMATE_CLAIMS => 'Tripmate Claims',
            self::GENERAL_INFORMATION => 'General Information',
        };
    }

    public static function getOptions(): array
    {
        return [
            self::PRIVACY_POLICY->value => self::PRIVACY_POLICY->label(),
            self::TERMS_AND_CONDITION->value => self::TERMS_AND_CONDITION->label(),
            self::TRAVEL_PROTECTION_PLAN_SUMMARY->value => self::TRAVEL_PROTECTION_PLAN_SUMMARY->label(),
            self::SCHEDULE_OF_BENEFITS_PLAN_COSTS->value => self::SCHEDULE_OF_BENEFITS_PLAN_COSTS->label(),
            self::CLAIM_PROCESS->value => self::CLAIM_PROCESS->label(),
            self::EMERGENCY_TRAVEL_ASSISTANCE_PLATINUM->value => self::EMERGENCY_TRAVEL_ASSISTANCE_PLATINUM->label(),
            self::EMERGENCY_TRAVEL_ASSISTANCE_SILVER->value => self::EMERGENCY_TRAVEL_ASSISTANCE_SILVER->label(),
            self::TRIPMATE_CLAIMS->value => self::TRIPMATE_CLAIMS->label(),
            self::GENERAL_INFORMATION->value => self::GENERAL_INFORMATION->label(),
        ];
    }
}
