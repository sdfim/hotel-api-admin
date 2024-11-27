<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductAffiliationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

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
        'affiliation_name',
        'combinable',
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

    public function details(): HasMany
    {
        return $this->hasMany(ProductAffiliationDetail::class, 'affiliation_id');
    }
}
