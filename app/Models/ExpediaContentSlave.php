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

    /**
     * @var string
     */
    protected $primaryKey = 'property_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected const TABLE = 'expedia_content_slave';

    protected $table = 'expedia_content_slave';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
}
