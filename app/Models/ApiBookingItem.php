<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;


class ApiBookingItem extends Model
{
    use HasFactory;

	/**
     * @var string
     */
    protected $table = 'api_booking_items';
	protected $primaryKey = 'booking_item';
	public $incrementing = false;

	public $timestamps = false;
 	/**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
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

    public function search(): BelongsTo
    {
        return $this->belongsTo(ApiSearchInspector::class, 'search_id', 'search_id');
    }
 	/**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
	
	protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::delete($model->response_path);
			Storage::delete($model->client_response_path);
        });
    }

}
