<?php

namespace App\Actions\DepositInformation;

use App\Models\DepositInformation;

class AddDepositInformation
{
    public function createWithConditions(array $data): DepositInformation
    {
        $DepositInformation = DepositInformation::create($data);

        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                $DepositInformation->conditions()->create($condition);
            }
        }

        return $DepositInformation;
    }
}
