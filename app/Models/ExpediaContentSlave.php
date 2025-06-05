<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $primaryKey = 'expedia_property_id';

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
            'rooms_occupancy' => 'array',
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

    public function mapperGiataExpedia(): HasMany
    {
        return $this->hasMany(Mapping::class, 'supplier_id', 'expedia_property_id')->expedia();
    }
}
