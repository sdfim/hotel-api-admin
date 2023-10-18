<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiExceptionReport extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'supplier_id',
        'type',
        'request',
        'response_path'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Suppliers::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }
}
