<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceProviderDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'type_document',
        'uri',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }
}
