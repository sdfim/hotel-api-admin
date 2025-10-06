<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApiBookingItem extends Model
{
    use HasFactory;

    protected $table = 'api_booking_items';

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
        'email_verified',
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

    // original booking_item that was booked during first search
    public function firstBookingItem(): BelongsTo
    {
        return $this->belongsTo(ApiBookingItem::class, 'booking_item', 'checked_booking_item');
    }

    // new booking_item that was checked during check-quote after second search
    public function secondBookingItem(): BelongsTo
    {
        return $this->belongsTo(ApiBookingItem::class, 'checked_booking_item', 'booking_item');
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::delete($model->response_path);
            Storage::delete($model->client_response_path);
        });
    }
}
