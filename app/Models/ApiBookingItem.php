<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApiBookingItem extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_booking_items';

    /**
     * @var string
     */
    protected $primaryKey = 'booking_item';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'booking_item',
        'search_id',
        'supplier_id',
        'booking_item_data',
        'created_at'
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
