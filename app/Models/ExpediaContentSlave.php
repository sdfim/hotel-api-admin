<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpediaContentSlave extends Model
{
    use HasFactory;
	/**
     * @var mixed
     */
    protected $connection;
	protected $primaryKey = 'property_id';
	public $incrementing = false;
	protected const TABLE = 'expedia_content_slave';

    /**
     * @var string[]
     */
    protected $casts = [
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

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . self::TABLE;
    }
}
