<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\Sanctum;

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
        'access_token',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Sanctum::$personalAccessTokenModel);
    }
}
