<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigServiceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductInformativeServiceFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductInformativeService extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductInformativeServiceFactory::new();
    }

    protected $table = 'pd_product_informative_services';

    protected $fillable = [
        'product_id',
        'rate_id',
        'room_id',
        'service_id',
        'start_date',
        'end_date',
        'cost',
        'total_net',
        'apply_type',
        'name',
        'currency',
        'service_time',
        'show_service_on_pdf',
        'show_service_data_on_pdf',
        'commissionable',
        'auto_book',
        'age_from',
        'age_to',
        'min_night_stay',
        'max_night_stay',
    ];

    protected $hidden = [
        'pivot',
        'show_service_on_pdf' => 'boolean',
        'show_service_data_on_pdf' => 'boolean',
        'auto_book' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function setServiceTimeAttribute($value)
    {
        try {
            $this->attributes['service_time'] = Carbon::createFromFormat('h:i A', $value)->format('H:i');
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            $this->attributes['service_time'] = null;
        }
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(HotelRate::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ConfigServiceType::class);
    }

    public function dynamicColumns(): HasMany
    {
        return $this->hasMany(ProductInformativeServiceDynamicColumn::class, 'product_informative_service_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'service_id', 'cost'])
            ->logOnlyDirty()
            ->useLogName('product_informative_service');
    }
}
