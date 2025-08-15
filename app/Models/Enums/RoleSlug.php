<?php

namespace App\Models\Enums;

enum RoleSlug: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case EXTERNAL_USER = 'external-user';
    case API_USER = 'api-user';
}
