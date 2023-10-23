<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'token_id',
        'access_token'
    ];

    /**
     * @param $token
     * @return int|null
     */
    public function getTokenId($token): int|null
    {
        return Channel::where('access_token', 'like', '%' . $token)->first()->token_id ?? null;
    }
}


