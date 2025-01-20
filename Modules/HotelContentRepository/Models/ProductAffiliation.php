<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductAffiliationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use App\Models\Configurations\ConfigConsortium;

class ProductAffiliation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductAffiliationFactory::new();
    }

    protected $table = 'pd_product_affiliations';

    protected $fillable = [
        'product_id',
        'consortia_id',
        'description',
        'start_date',
        'end_date',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function consortia(): BelongsTo
    {
        return $this->belongsTo(ConfigConsortium::class);
    }
}
