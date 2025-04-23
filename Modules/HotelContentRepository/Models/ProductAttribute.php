<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductAttributeFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductAttribute extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductAttributeFactory::new();
    }

    protected $table = 'pd_product_attributes';

    protected $fillable = [
        'product_id',
        'config_attribute_id',
        'config_attribute_category_id',
    ];

    protected $hidden = [
        'pivot',
    ];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ConfigAttribute::class, 'config_attribute_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ConfigAttributeCategory::class, 'config_attribute_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'config_attribute_id'])
            ->logOnlyDirty()
            ->useLogName('product_attribute');
    }
}
