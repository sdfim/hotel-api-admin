<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InsuranceProviderDocumentation
 *
 * @property int $id
 * @property int $provider_id
 * @property string $type_document
 * @property string $uri
 *
 * @property InsuranceProvider $provider
 */
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
