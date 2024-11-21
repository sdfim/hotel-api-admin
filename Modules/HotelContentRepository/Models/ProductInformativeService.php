<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductInformativeServiceFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductInformativeService extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductInformativeServiceFactory::new();
    }

    protected $table = 'pd_product_informative_services';

    protected $fillable = [
        'product_id',
        'service_id',
    ];

    protected $hidden = [
        'pivot'
    ];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ConfigServiceType::class);
    }
}
