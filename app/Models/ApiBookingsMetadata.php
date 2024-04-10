<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiBookingsMetadata extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_bookings_metadata';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
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
        'booking_id',
        'supplier_id',
        'supplier_booking_item_id',
        'booking_item_data',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'booking_item_data' => 'array',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_item_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
