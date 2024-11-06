<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;
use App\Policies\Base\BasePolicy;

class ChannelPolicy extends BasePolicy
{
    protected static string $prefix = 'channel';

    public function view(User $user, ?Channel $channel = null): bool
    {
        return $this->canByUser('view', $user, $channel);
    }

    public function update(User $user, ?Channel $channel = null): bool
    {
        return $this->canByUser('update', $user, $channel);
    }

    public function delete(User $user, ?Channel $channel = null): bool
    {
        return $this->canByUser('delete', $user, $channel);
    }

    private function canByUser(string $name, User $user, ?Channel $channel = null): bool
    {
        if (!$channel) {
            return $this->can('update', $user);
        }

        return $this->can('update', $user) && ($channel->token?->tokenable_id == $user->id || $user->hasRole('admin'));
    }
}
