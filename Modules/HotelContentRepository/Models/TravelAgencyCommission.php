<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\HotelContentRepository\Models\Factories\TravelAgencyCommissionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TravelAgencyCommission extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'commission_id',
        'commission_value',
        'commission_value_type',
        'date_range_start',
        'date_range_end',
        'room_type',
        'rate_type',
        'consortia',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected $table = 'pd_travel_agency_commissions';

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class, 'commission_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(TravelAgencyCommissionCondition::class, 'travel_agency_commissions_id');
    }

    public function consortia(): BelongsToMany
    {
        return $this->belongsToMany(
            ConfigConsortium::class,
            'pd_travel_agency_commissions_consortia',
            'pd_travel_agency_commission_id',
            'config_consortia_id'
        );
    }

    public function getConfigConsortiaByIds()
    {
        $ids = $this->consortia instanceof Collection
            ? $this->consortia->pluck('id')->toArray()
            : (is_array($this->consortia) ? $this->consortia : []);

        return ConfigConsortium::whereIn('id', $ids)->get();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'name', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'])
            ->logOnlyDirty()
            ->useLogName('travel_agency_commission');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected function casts(): array
    {
        return [
            'date_range_start' => 'date',
            'date_range_end'   => 'date',
        ];
    }

    protected static function newFactory()
    {
        return TravelAgencyCommissionFactory::new();
    }
}
