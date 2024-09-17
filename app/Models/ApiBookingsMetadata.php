<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Modules\Enums\SupplierNameEnum;

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
        'hotel_supplier_id',
        'booking_item_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_item_data' => 'array',
        ];
    }

    public function getSearchTypeAttribute()
    {
        return 'hotel';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function hotel(): HasOneThrough
    {
        return match($this->supplier->name){
            SupplierNameEnum::HBSI->value => $this->hasOneThrough(
                Property::class,
                Mapping::class,
                'supplier_id', // Foreign key on the Mappings table...
                'code', // Foreign key on the Properties table...
                'hotel_supplier_id', // Local key on the ApiBookingsMetadata table...
                'giata_id' // Local key on the Mappings table...
            ),
            SupplierNameEnum::EXPEDIA->value => $this->hasOneThrough(
                Property::class,
                Mapping::class,
                'supplier_id', // Foreign key on the Mappings table...
                'code', // Foreign key on the Properties table...
                'hotel_supplier_id', // Local key on the ApiBookingsMetadata table...
                'giata_id' // Local key on the Mappings table...
            ),
        };
    }
}
