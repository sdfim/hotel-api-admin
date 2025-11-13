<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\HotelContentRepository\Models\Factories\ProductFeeTaxFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Modules\HotelContentRepository\Models\Traits\HandlesRoomIds;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductFeeTax extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;
//    use HandlesRoomIds;

    protected static function newFactory()
    {
        return ProductFeeTaxFactory::new();
    }

    protected $table = 'pd_product_fees_and_taxes';

    protected $fillable = [
        'name',
        'product_id',
        'rate_id',
        'room_id',
        'group_rooms',
        'start_date',
        'end_date',
        'net_value',
        'rack_value',
        'type',
        'value_type',
        'commissionable',
        'currency',
        'collected_by',
        'fee_category',
        'apply_type',
        'supplier_id',
        'action_type',
        'old_name',
        'age_from',
        'age_to',
        'currency',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'net_value' => 'float',
            'rack_value' => 'float',
            'commissionable' => 'boolean',
            'apply_type' => ProductApplyTypeEnum::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(HotelRate::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

//    public function productRoomLinks()
//    {
//        return $this->morphMany(ProductRoomLink::class, 'linkable');
//    }

    public function getRoomsAttribute(): array
    {
        return $this->productRoomLinks->pluck('room_id')->toArray();
    }

    public function setRoomsAttribute($value): void
    {
        $this->rooms = is_array($value) ? $value : [$value];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('product_fee_tax');
    }
}
