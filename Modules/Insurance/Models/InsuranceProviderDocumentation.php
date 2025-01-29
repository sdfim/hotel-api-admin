<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Vendor;

/**
 * Class InsuranceProviderDocumentation
 *
 * @property int $id
 * @property int $provider_id
 * @property string $document_type
 * @property string $viewable
 * @property string $path
 *
 * @property InsuranceProvider $provider
 */
class InsuranceProviderDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'document_type',
        'viewable',
        'path',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
