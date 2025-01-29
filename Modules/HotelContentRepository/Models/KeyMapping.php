<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\KeyMappingFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KeyMapping extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return KeyMappingFactory::new();
    }

    protected $table = 'pd_key_mapping';

    protected $fillable = [
        'product_id',
        'key_id',
        'key_mapping_owner_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function keyMappingOwner(): BelongsTo
    {
        return $this->belongsTo(KeyMappingOwner::class, 'key_mapping_owner_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'key_id', 'key_mapping_owner_id'])
            ->logOnlyDirty()
            ->useLogName('key_mapping');
    }
}
