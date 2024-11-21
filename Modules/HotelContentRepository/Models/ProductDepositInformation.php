<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductDepositInformationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductDepositInformation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductDepositInformationFactory::new();
    }

    protected $table = 'pd_product_deposit_information';

    protected $fillable = [
        'product_id',
        'days_departure',
        'pricing_parameters',
        'pricing_value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
