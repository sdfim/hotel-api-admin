<?php

namespace Modules\HotelContentRepository\Actions\Insurance;

use Modules\Insurance\Models\InsurancePlan;

class AddInsurance
{
    public function handle(InsurancePlan $insurance)
    {
        $insurance->save();

        return $insurance;
    }
}
