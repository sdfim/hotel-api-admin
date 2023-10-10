<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiBookingInspector extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'token_id',
        'search_id',
		'supplier_id',
        'type',
		'sub_type',
        'request',
        'response_path',
		'client_response_path'
    ];

    protected static function booted (): void
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
