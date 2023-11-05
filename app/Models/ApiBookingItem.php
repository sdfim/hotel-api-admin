<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiBookingItem extends Model
{
    use HasFactory;

	/**
     * @var string
     */
    protected $table = 'api_booking_items';

	public $timestamps = false;

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

}
