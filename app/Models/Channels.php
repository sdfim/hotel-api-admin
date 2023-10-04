<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channels extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'token_id',
        'access_token'
    ];

	public function getTokenId($token)
	{
		return Channels::where('access_token', 'like', '%'.$token)->first()->token_id ?? null;
	}
}


