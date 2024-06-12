<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class ApiBookingInspector extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_booking_inspector';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'booking_id',
        'token_id',
        'search_id',
        'supplier_id',
        'search_type',
        'booking_item',
        'status',
        'status_describe',
        'type',
        'sub_type',
        'request',
        'response_path',
        'client_response_path',
    ];

    /**
     * @return BelongsTo
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo
     */
    public function search(): BelongsTo
    {
        return $this->belongsTo(ApiSearchInspector::class, 'search_id', 'search_id');
    }

    public function metadata()
    {
        return $this->belongsTo(ApiBookingsMetadata::class, 'booking_item' , 'booking_item');
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
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
