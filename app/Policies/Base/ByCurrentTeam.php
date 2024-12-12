<?php

namespace App\Policies\Base;

use App\Models\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $withRelation
 */
trait ByCurrentTeam
{
    public function update(User $user, ?Model $model = null): bool
    {
        return $this->canByTeam('update', $user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canByTeam('delete', $user, $model);
    }

    private function canByTeam(string $name, User $user, ?Model $model = null): bool
    {
        if (!$model) {
            return $this->can($name, $user);
        }

        if ($user->hasRole(RoleSlug::ADMIN->value)) return true;

        if ($this->withTeam($name, $user)) {
            if (!empty($this->withRelation)) {
                return $model->{$this->withRelation}()
                    ->where('vendor_id', $user->currentTeam->vendor_id)
                    ->exists();
            }

            return $model->vendor_id == $user->currentTeam->vendor_id;
        }

        $permission = $this->getPrefix().'.'.$name;

        return $user->hasPermission($permission);
    }
}
