<?php

namespace App\Policies\Base;

use App\Models\User;

class BasePolicy
{
    protected static string $prefix = 'base';

    protected static bool $withTeam = false;

    private static array $methods = [
        'view',
        'create',
        'update',
        'delete',
    ];

    protected function getPrefix(): string
    {
        return static::$prefix;
    }

    public function __call(string $name, array $arguments): bool
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } elseif (in_array($name, self::$methods)) {
            return $this->can($name, $arguments[0]);
        }

        return false;
    }

    protected function can(string $name, User $user): bool
    {
        $permission = $this->getPrefix().'.'.$name;

        return $user->hasPermission($permission) ||
            $this->withTeam($name, $user) ||
            $user->hasRole('admin');
    }

    protected function withTeam(string $name, User $user): bool
    {
        $withTeam = false;
        if (static::$withTeam) {
            $currentTeam = $user->currentTeam;
            $withTeam = $user->hasTeamPermission($currentTeam, $name);
        }

        return $withTeam;
    }
}
