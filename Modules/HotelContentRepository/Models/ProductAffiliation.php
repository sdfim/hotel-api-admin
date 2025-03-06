<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAmenity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductAffiliationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductAffiliation extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductAffiliationFactory::new();
    }

    protected $table = 'pd_product_affiliations';

    protected $fillable = [
        'product_id',
        'rate_id',
        'room_id',
        'start_date',
        'end_date',
        'consortia',
        'is_paid',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'consortia' => 'array',
        'is_paid' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(ProductAffiliationAmenity::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(HotelRate::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('product_affiliation');
    }
}
