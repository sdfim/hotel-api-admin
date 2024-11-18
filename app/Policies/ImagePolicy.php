<?php

namespace App\Policies;


use App\Policies\Base\BasePolicy;

class ImagePolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'hotel_image';
}
