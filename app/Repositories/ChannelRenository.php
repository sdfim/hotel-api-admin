<?php

namespace App\Repositories;

use App\Models\Channel;

class ChannelRenository
{
    public static function getTokenId($token): ?int
    {
        return Channel::where('access_token', 'like', '%'.$token)->first()->token_id ?? null;
    }

    public static function getTokenName($token): ?string
    {
        return Channel::where('access_token', 'like', '%'.$token)->first()->name ?? null;
    }
}
