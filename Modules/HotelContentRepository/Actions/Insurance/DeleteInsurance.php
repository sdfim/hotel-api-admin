<?php

namespace Modules\HotelContentRepository\Actions\Insurance;

use Modules\Insurance\Models\InsurancePlan;

class DeleteInsurance
{
    public function handle(InsurancePlan $insurance)
    {
        $insurance->applications()->delete();
        $insurance->delete();
    }
}
