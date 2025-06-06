<?php

namespace App\Actions\DepositInformation;

use App\Models\DepositInformation;

class EditDepositInformation
{
    public function updateWithConditions(DepositInformation $depositInformation, array $data): void
    {
        $depositInformation->update($data);

        if (isset($data['conditions'])) {
            $existingConditionIds = $depositInformation->conditions()->pluck('id')->toArray();
            $newConditionIds = array_filter(array_column($data['conditions'], 'id'));

            $conditionsToDelete = array_diff($existingConditionIds, $newConditionIds);
            $depositInformation->conditions()->whereIn('id', $conditionsToDelete)->delete();

            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                if (isset($condition['id'])) {
                    $depositInformation->conditions()->updateOrCreate(['id' => $condition['id']], $condition);
                } else {
                    $depositInformation->conditions()->create($condition);
                }
            }
        }
    }
}
