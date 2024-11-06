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
 * @property string $document_type
 * @property string $path
 *
 * @property InsuranceProvider $provider
 */
class InsuranceProviderDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'document_type',
        'path',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }
}
