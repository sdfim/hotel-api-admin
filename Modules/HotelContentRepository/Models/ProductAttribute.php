<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductAttributeFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductAttribute extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductAttributeFactory::new();
    }

    protected $table = 'pd_product_attributes';

    protected $fillable = [
        'product_id',
        'config_attribute_id',
    ];

    protected $hidden = [
        'pivot'
    ];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ConfigAttribute::class);
    }
}
