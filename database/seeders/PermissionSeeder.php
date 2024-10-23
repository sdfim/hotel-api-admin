<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    private static array $prefixPermissions = [
        'channel',
        'pricing_rule',
        'supplier',
        'general_configuration',
        'giata_geography',
        'api_search_inspector',
        'api_booking_inspector',
        'api_booking_item',
        'api_exception_report',
        'property',
        'reservation',
        'property_weighting',
        'ice_portal_propery',
        'expedia_content',
        'mapping',
        'user',
        'role',
        'permission',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionTypes = ['view', 'create', 'update', 'delete'];

        foreach (self::$prefixPermissions as $prefix) {
            $modelName = Str::ucfirst(Str::camel($prefix));

            foreach ($permissionTypes as $type) {
                $slug = $prefix . "." . $type;

                if (!Permission::where('slug', $slug)->exists()) {
                    $permission = new Permission();
                    $permission->slug = $slug;
                    $permission->name = $modelName . ' ' . $type;
                    $permission->save();
                }
            }
        }

        // Assign all permissions to the 'Admin' role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $permissions = Permission::all();
            $adminRole->permissions()->sync($permissions->pluck('id')->toArray());
        }
    }
}
