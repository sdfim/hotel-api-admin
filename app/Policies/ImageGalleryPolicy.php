<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ImageGalleryPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'image_gallery';
}
