<?php

namespace Modules\HotelContentRepository\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductCancellationPolicyFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductCancellationPolicy extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductCancellationPolicyFactory::new();
    }

    protected $table = 'pd_product_cancellation_policies';

    protected $fillable = [
        'product_id',
        'name',
        'start_date',
        'expiration_date',
        'manipulable_price_type',
        'price_value',
        'price_value_type',
        'price_value_target',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(ProductCancellationPolicyCondition::class, 'product_cancellation_policy_id', 'id');
    }
}
