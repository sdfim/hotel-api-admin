<?php

namespace App\Actions\DepositInformation;

use App\Models\DepositInformation;


class DeleteDepositInformation
{
    public function handle(DepositInformation $depositInformation)
    {
        $depositInformation->delete();
    }
}
