<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspector extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_search_inspector';
	protected $primaryKey = 'search_id';
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
        'search_id',
        'token_id',
        'suppliers',
        'search_type',
        'type',
        'request',
        'response_path',
        'client_response_path'
    ];

    /**
     * @return BelongsTo
     */
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
