<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\HotelContentRepository\Models\Factories\TravelAgencyCommissionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TravelAgencyCommission extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return TravelAgencyCommissionFactory::new();
    }

    protected $table = 'pd_travel_agency_commissions';

    protected $fillable = [
        'product_id',
        'name',
        'commission_value',
        'commission_value_type',
        'date_range_start',
        'date_range_end',
        'room_type',
        'consortia',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end' => 'date',
        'consortia' => 'array',
    ];

    public function product(): HasOne
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(TravelAgencyCommissionCondition::class, 'travel_agency_commissions_id');
    }

    public function getConsortiaAttribute($value): ?array
    {
        return json_decode($value, true);
    }

    public function setConsortiaAttribute($value): void
    {
        $this->attributes['consortia'] = json_encode($value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'name', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'])
            ->logOnlyDirty()
            ->useLogName('travel_agency_commission');
    }
}
