<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInformativeServiceDynamicColumn extends Model
{
    use HasFactory;

    protected $table = 'pd_product_informative_services_dynamic_columns';

    protected $fillable = [
        'product_informative_service_id',
        'name',
        'value',
        'show_on_invoice',
        'show_on_itinerary',
        'show_on_vendor_manifest',
    ];

    protected $casts = [
        'show_on_invoice' => 'boolean',
        'show_on_itinerary' => 'boolean',
        'show_on_vendor_manifest' => 'boolean',
    ];

    public $timestamps = false;

    public function productInformativeService(): BelongsTo
    {
        return $this->belongsTo(ProductInformativeService::class);
    }
}
