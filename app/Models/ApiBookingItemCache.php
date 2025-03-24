<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApiBookingItemCache extends Model
{
    use HasFactory;

    protected $table = 'api_booking_item_cache';

    protected $primaryKey = 'booking_item';

    public $incrementing = false;

    public $timestamps = false;

    public function getKeyType(): string
    {
        return 'string';
    }

    protected $fillable = [
        'booking_item',
        'search_id',
        'supplier_id',
        'booking_item_data',
        'created_at',
        'complete_id',
        'cache_checkpoint',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'child_items' => 'array',
    ];

    public function search(): BelongsTo
    {
        return $this->belongsTo(ApiSearchInspector::class, 'search_id', 'search_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
