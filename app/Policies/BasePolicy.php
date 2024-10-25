<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    protected static string $prefix = 'base';

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
        return $user->hasPermission($this->getPrefix().'.'.$name) || $user->hasRole('admin');
    }
}
