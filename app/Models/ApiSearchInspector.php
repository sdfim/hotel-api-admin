<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class ApiSearchInspector extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_search_inspector';

    /**
     * @var string
     */
    protected $primaryKey = 'search_id';

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
        'search_id',
        'status',
        'status_describe',
        'token_id',
        'suppliers',
        'search_type',
        'type',
        'request',
        'response_path',
        'original_path',
        'client_response_path',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    /**
     * Get the search for the bookingItem.
     */
    public function bookingItem(): HasMany
    {
        return $this->hasMany(ApiBookingItem::class, 'search_id', 'search_id');
    }

    /**
     * Get the search for the bookingInspector.
     */
    public function apiBookingInspector(): HasMany
    {
        return $this->hasMany(ApiBookingInspector::class, 'search_id', 'search_id');
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
