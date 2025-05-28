<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductDepositInformationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductDepositInformation extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductDepositInformationFactory::new();
    }

    protected $table = 'pd_product_deposit_information';

    protected $fillable = [
        'product_id',
        'rate_id',
        'name',
        'start_date',
        'expiration_date',
        'manipulable_price_type',
        'price_value',
        'price_value_type',
        'price_value_target',
        'days_after_booking_initial_payment_due',
        'days_before_arrival_initial_payment_due',
        'date_initial_payment_due',
        'initial_payment_due_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiration_date' => 'date',
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
        return $this->hasMany(ProductDepositInformationCondition::class, 'product_deposit_information_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('product_deposit_information');
    }
}
