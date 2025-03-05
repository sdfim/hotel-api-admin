<?php

namespace Modules\HotelContentRepository\Actions\Insurance;

use Modules\Insurance\Models\InsuranceApplication;

class AddInsuranceApplication
{
    public function handle(InsuranceApplication $insuranceApplication): ?InsuranceApplication
    {
        $insuranceApplication->save();

        return $insuranceApplication;
    }

    public function insert(array $insuranceApplication): bool
    {
        return InsuranceApplication::insert($insuranceApplication);
    }
}
