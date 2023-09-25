<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpediaContent extends Model
{
    use HasFactory;

	protected $connection= 'mysql2';

	protected $casts = [
        'address' => 'array',
        'ratings' => 'array',
		'location' => 'array',
        'category' => 'array',
		'business_model' => 'array',
		'checkin' => 'array',
		'checkout' => 'array',
		'fees' => 'array',
		'policies' => 'array',
		'attributes' => 'array',
		'amenities' => 'array',
		'images' => 'array',
		'onsite_payments' => 'array',
		'rooms' => 'array',
		'rates' => 'array',
		'dates' => 'array',
		'descriptions' => 'array',
		'themes' => 'array',
		'chain' => 'array',
		'brand' => 'array',
		'statistics' => 'array',
		'vacation_rental_details' => 'array',
		'airports' => 'array',
		'spoken_languages' => 'array',
		'all_inclusive' => 'array',
    ];
}
