<?php

namespace Database\Seeders;

use App\Models\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleSlug::cases() as $roleSlug) {
            Role::firstOrCreate(
                ['slug' => $roleSlug->value],
                ['name' => Str::title(str_replace('-', ' ', $roleSlug->value))]
            );
        }
    }
}
