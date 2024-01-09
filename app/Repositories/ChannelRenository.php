<?php

namespace App\Repositories;

use App\Models\Channel;

class ChannelRenository
{
    /**
     * @param $token
     * @return int|null
     */
    public static function getTokenId($token): int|null
    {
        return Channel::where('access_token', 'like', '%' . $token)->first()->token_id ?? null;
    }

    public static function getTokenName($token): string|null
    {
        return Channel::where('access_token', 'like', '%' . $token)->first()->name ?? null;
    }
}
