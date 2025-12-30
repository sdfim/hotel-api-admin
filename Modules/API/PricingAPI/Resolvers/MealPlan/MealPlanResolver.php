<?php

namespace Modules\API\PricingAPI\Resolvers\MealPlan;

use Modules\HotelContentRepository\Models\MealPlanMapping;

class MealPlanResolver
{
    /**
     * Cache for meal plan mappings per hotel (giata_id).
     *
     * @var array<int, array<string, string>>
     */
    private array $mealPlanMappingsCache = [];

    /**
     * Resolve final meal plan name for given hotel and rate.
     *
     * Priority:
     *  0) If BOTH codes are present -> DB mapping by BOTH columns (AND logic)
     *  1) DB mapping by meal_plan_code_from_supplier (exact / case-insensitive)
     *  2) DB mapping by rate_plan_code_from_supplier (RatePlanCode)
     *  3) fallback to constant MEAL_PLAN or raw value (handled in caller)
     */
    public function resolveMealPlanName(int $giataId, string $mealPlanRaw, string $ratePlanCode): string
    {
        // 0) AND logic: both codes present in supplier data
        if ($mealPlanRaw !== '' && $ratePlanCode !== '') {
            $mapped = $this->findMealPlanInDbByBoth($giataId, $mealPlanRaw, $ratePlanCode);

            if ($mapped !== null) {
                return $mapped;
            }
        }

        // 1) Try mapping by explicit meal plan code from supplier (OR logic - as before)
        if ($mealPlanRaw !== '') {
            $mapped = $this->findMealPlanInDb($giataId, $mealPlanRaw, 'meal_plan_code_from_supplier');
            if ($mapped !== null) {
                return $mapped;
            }

            // Try case-insensitive look-up
            $mapped = $this->findMealPlanInDb($giataId, $mealPlanRaw, 'meal_plan_code_from_supplier', true);
            if ($mapped !== null) {
                return $mapped;
            }
        }

        // 2) If supplier did not send explicit meal plan code,
        //    or it wasn't mapped, try mapping by rate plan code (OR logic - as before)
        if ($ratePlanCode !== '') {
            $mapped = $this->findMealPlanInDb($giataId, $ratePlanCode, 'rate_plan_code_from_supplier');
            if ($mapped !== null) {
                return $mapped;
            }
        }

        return '';
    }

    private function findMealPlanInDb(
        int $giataId,
        string $needle,
        string $column,
        bool $caseInsensitive = false
    ): ?string {
        // Allow matching only by these two supported columns
        if (! in_array($column, ['meal_plan_code_from_supplier', 'rate_plan_code_from_supplier'], true)) {
            return null;
        }

        $rows = $this->getMealPlanMappingsForHotel($giataId);

        foreach ($rows as $row) {
            $mealValue = $row['meal_plan_code_from_supplier'] ?? null;
            $rateValue = $row['rate_plan_code_from_supplier'] ?? null;

            /**
             * IMPORTANT:
             * When searching ONLY by meal_plan_code_from_supplier,
             * skip rows where rate_plan_code_from_supplier is also filled.
             * Those rows must be handled exclusively by the AND-logic method.
             */
            if (
                $column === 'meal_plan_code_from_supplier' &&
                $rateValue !== null && $rateValue !== ''
            ) {
                continue;
            }

            /**
             * Symmetric case:
             * When searching ONLY by rate_plan_code_from_supplier,
             * skip rows where meal_plan_code_from_supplier is also filled.
             */
            if (
                $column === 'rate_plan_code_from_supplier' &&
                $mealValue !== null && $mealValue !== ''
            ) {
                continue;
            }

            $value = $row[$column] ?? null;

            // Skip empty values
            if ($value === null || $value === '') {
                continue;
            }

            if ($caseInsensitive) {
                if (mb_strtolower((string) $value) === mb_strtolower($needle)) {
                    return $row['our_meal_plan'] ?? null;
                }
            } else {
                if ((string) $value === $needle) {
                    return $row['our_meal_plan'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Find meal plan in pd_meal_plan_mappings table by BOTH codes.
     *
     * This is used when both meal plan code and rate plan code are present.
     * In this case we want AND logic:
     *  - mapping row must have BOTH columns filled
     *  - BOTH values must match the supplier values.
     */
    private function findMealPlanInDbByBoth(
        int $giataId,
        string $mealPlanRaw,
        string $ratePlanCode
    ): ?string {
        $rows = $this->getMealPlanMappingsForHotel($giataId);

        foreach ($rows as $row) {
            $mealValue = $row['meal_plan_code_from_supplier'] ?? null;
            $rateValue = $row['rate_plan_code_from_supplier'] ?? null;

            // We only consider rows where BOTH columns are filled
            if (
                $mealValue === null || $mealValue === '' ||
                $rateValue === null || $rateValue === ''
            ) {
                continue;
            }

            // First try strict match
            if ($mealValue === $mealPlanRaw && $rateValue === $ratePlanCode) {
                return $row['our_meal_plan'] ?? null;
            }

            // Then try case-insensitive for meal plan code (rate plan stays exact)
            if (
                mb_strtolower($mealValue) === mb_strtolower($mealPlanRaw) &&
                $rateValue === $ratePlanCode
            ) {
                return $row['our_meal_plan'] ?? null;
            }
        }

        return null;
    }

    /**
     * Load mappings for a given hotel (giata_id) once and cache them in memory.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getMealPlanMappingsForHotel(int $giataId): array
    {
        if (! isset($this->mealPlanMappingsCache[$giataId])) {
            $this->mealPlanMappingsCache[$giataId] = MealPlanMapping::query()
                ->where('giata_id', $giataId)
                ->where('is_enabled', true)
                ->get()
                ->toArray();
        }

        return $this->mealPlanMappingsCache[$giataId];
    }
}
