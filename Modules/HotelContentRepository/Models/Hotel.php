<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\HotelContentRepository\Models\Factories\HotelFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Hotel extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return HotelFactory::new();
    }

    protected $table = 'pd_hotels';

    protected $fillable = [
        'giata_code',
        'featured_flag',
        'weight',
        'is_not_auto_weight',
        'sale_type',
        'address',
        'star_rating',
        'num_rooms',
        'room_images_source_id',
        'hotel_board_basis',
        'travel_agent_commission',
    ];

    protected $casts = [
        'address' => 'array',
        'location' => 'array',
        'hotel_board_basis' => 'array',
        'travel_agent_commission' => 'float',
        'is_not_auto_weight' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function giataCode(): HasOne
    {
        return $this->hasOne(Property::class, 'code', 'giata_code');
    }

    public function roomImagesSource(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class, 'room_images_source_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(HotelRate::class);
    }

    public function webFinders(): BelongsToMany
    {
        return $this->belongsToMany(HotelWebFinder::class, 'pd_hotel_web_finder_hotel', 'hotel_id', 'web_finder_id');
    }

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'related');
    }

    public function contentSource(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('hotel');
    }
}
