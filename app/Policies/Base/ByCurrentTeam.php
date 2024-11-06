<?php

namespace App\Policies\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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

        return $this->can($name, $user) && $model->team_id == $user->current_team_id;
    }
}
