<?php

namespace App\Repositories;

use App\Models\Channel;

class CannelRenository
{
    /**
     * @param $token
     * @return int|null
     */
    public static function getTokenId($token): int|null
    {
        return Channel::where('access_token', 'like', '%' . $token)->first()->token_id ?? null;
    }
}
