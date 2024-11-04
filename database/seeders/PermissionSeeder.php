<?php

namespace Database\Seeders;

use App\Models\Permission;
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
        'hotel',
        'insurance_provider',
        'insurance_restriction',
        'config_attribute',
        'config_consortium',
        'config_descriptive_type',
        'config_job_description',
        'config_service_type',
        'config_chain',
        'image_gallery',
        'hotel_image',
    ];

    private static array $permissions = [
        'statistic-charts',
        'swagger-docs',
        'log-viewer',
        'config-group',
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
                $this->createIfNotExists(
                    slug: $prefix . "." . $type,
                    name: $modelName . ' ' . $type,
                );
            }
        }

        foreach (self::$permissions as $permission) {
            $this->createIfNotExists(
                slug: $permission,
                name: Str::replace(['-', '_'], ' ', Str::ucfirst($permission)),
            );
        }
    }

    private function createIfNotExists(string $slug, string $name): void
    {
        if (!Permission::where('slug', $slug)->exists()) {
            $permission = new Permission();
            $permission->slug = $slug;
            $permission->name = $name;
            $permission->save();
        }
    }
}
