<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class ApiInspector extends Model
{
    use HasFactory;

	public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

	protected $fillable = [
		'id',
		'token_id',
		'supplier_id',
		'type',
		'request',
		'response_path'
	];

	protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }
	

    public function token ()
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    public function supplier ()
    {
        return $this->belongsTo(Suppliers::class);
    }

}
