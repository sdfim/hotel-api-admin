<?php

namespace App\Policies;

use App\Models\Enums\RoleSlug;
use App\Models\User;
use App\Policies\Base\BasePolicy;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Vendor;

class VendorPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'vendor';

    public function create(User $user): bool
    {
        $permission = $this->getPrefix().'.create';

        return $user->hasPermission($permission) || $user->hasRole(RoleSlug::ADMIN->value);
    }

    public function update(User $user, ?Vendor $vendor = null): bool
    {
        return $user->hasRole(RoleSlug::ADMIN->value) || $this->hasTeamRoleAdmin($user, $vendor);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->hasRole(RoleSlug::ADMIN->value) || $this->hasTeamRoleAdmin($user, $vendor);
    }

    private function hasTeamRoleAdmin(User $user, ?Vendor $vendor = null): bool
    {
        return $vendor && ($vendor->team?->user_id == $user->id ||
            DB::table('team_user')
                ->where('user_id', $user->id)
                ->where('team_id', $vendor->team?->id)
                ->where('role', 'admin')
                ->exists());
    }
}
