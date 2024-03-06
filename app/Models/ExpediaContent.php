<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExpediaContent extends Model
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

    /**
     *
     */
    public const TABLE = 'expedia_content_main';

    /**
     * @var string[]
     */
    protected $fillable = [
        'property_id',
        'name',
        'address',
        'ratings',
        'location',
        'latitude',
        'longitude',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'address' => 'array',
        'ratings' => 'array',
        'location' => 'array',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . self::TABLE;
    }

    /**
     * @return string[]
     */
    public static function getFullListFields(): array
    {
        return [
            'property_id', 'name', 'address', 'ratings', 'location',
            'category', 'business_model', 'checkin', 'checkout',
            'fees', 'policies', 'attributes', 'amenities',
            'onsite_payments', 'rates',
            'images', 'rooms',
            'dates', 'descriptions', 'themes', 'chain', 'brand',
            'statistics', 'vacation_rental_details', 'airports',
            'spoken_languages', 'all_inclusive', 'rooms_occupancy',
            'total_occupancy', 'city', 'rating'
        ];
    }

    /**
     * @return string[]
     */
    public static function getShortListFields(): array
    {
        return [
            'property_id', 'name', 'images', 'location', 'amenities', 'rating',
        ];
    }

    /**
     * @return HasMany
     */
    public function mapperGiataExpedia(): HasMany
    {
        return $this->hasMany(MapperExpediaGiata::class, 'expedia_id', 'property_id');
    }

    /**
     * @return HasOne
     */
    public function expediaSlave(): HasOne
    {
        return $this->hasOne(ExpediaContentSlave::class, 'expedia_property_id', 'property_id');
    }
}
