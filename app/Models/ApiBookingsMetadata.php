<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'status', // status booking flow (booked, cancelled, modified, etc.)
        'retrieve', // RS API retrieve response for fast access
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
            'retrieve' => 'array',
            'status' => BookingStatusEnum::class, // cast status as enum
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
        if (
            ! $this->supplier || ! in_array($this->supplier->name, [
                SupplierNameEnum::HBSI->value,
                SupplierNameEnum::HOTEL_TRADER->value,
                SupplierNameEnum::EXPEDIA->value,
                SupplierNameEnum::ORACLE->value,
            ])
        ) {
            return $this->hasOneThrough(
                Property::class,
                Mapping::class,
                'supplier_id',
                'code',
                'hotel_supplier_id',
                'giata_id'
            )->whereRaw('1 = 0'); // Always false condition to return an empty relationship
        }

        return $this->hasOneThrough(
            Property::class,
            Mapping::class,
            'supplier_id', // Foreign key on the mappings table
            'code', // Foreign key on the properties table
            'hotel_supplier_id', // Local key on the current model
            'giata_id' // Local key on the mappings table
        )->where('mappings.supplier', $this->supplier->name);
    }

    public function inspector(): HasOne
    {
        return $this->hasOne(ApiBookingInspector::class, 'booking_item', 'booking_item')
            ->where('type', 'book')
            ->where('sub_type', 'create');
    }

    public function contentHotel(): HasOneThrough
    {
        if (
            ! $this->supplier || ! in_array($this->supplier->name, [
                \Modules\Enums\SupplierNameEnum::HBSI->value,
                \Modules\Enums\SupplierNameEnum::HOTEL_TRADER->value,
                \Modules\Enums\SupplierNameEnum::EXPEDIA->value,
                \Modules\Enums\SupplierNameEnum::ORACLE->value,
            ])
        ) {
            return $this->hasOneThrough(
                \Modules\HotelContentRepository\Models\Hotel::class,
                Mapping::class,
                'supplier_id',
                'giata_code',
                'hotel_supplier_id',
                'giata_id'
            )->whereRaw('1 = 0');
        }

        return $this->hasOneThrough(
            \Modules\HotelContentRepository\Models\Hotel::class,
            Mapping::class,
            'supplier_id',
            'giata_code',
            'hotel_supplier_id',
            'giata_id'
        )->where('mappings.supplier', $this->supplier->name);
    }
}
