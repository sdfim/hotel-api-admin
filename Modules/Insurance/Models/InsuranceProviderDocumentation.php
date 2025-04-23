<?php

namespace Modules\Insurance\Models;

use App\Models\Configurations\ConfigInsuranceDocumentationType;
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
 * @property InsuranceProvider $provider
 */
class InsuranceProviderDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'document_type_id',
        'viewable',
        'path',
    ];

    protected $casts = [
        'viewable' => 'array',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(ConfigInsuranceDocumentationType::class, 'document_type_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
