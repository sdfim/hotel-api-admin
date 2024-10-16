<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'last_updated',
        'name',
        'chain',
        'city',
        'city_id',
        'locale',
        'locale_id',
        'address',
        'mapper_address',
        'mapper_postal_code',
        'mapper_phone_number',
        'phone',
        'position',
        'rating',
        'latitude',
        'longitude',
        'url',
        'cross_references',
        'source',
        'property_auto_updates',
        'content_auto_updates',
    ];

    protected $table = 'properties';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $cacheDB = config('database.connections.mysql_cache.database');
        $this->table = "$cacheDB.properties";
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
            'chain' => 'json',
            'address' => 'json',
            'phone' => 'json',
            'position' => 'json',
            'cross_references' => 'json',
            'rating' => 'double',
            'url' => 'array'
        ];
    }

    public function mappings()
    {
        return $this->hasMany(Mapping::class, 'giata_id', 'code');
    }

    public function mapperExpediaGiata(): HasOne
    {
        return $this->hasOne(Mapping::class, 'giata_id', 'code')
            ->expedia();
    }

    public function giataGeography(): HasOne
    {
        return $this->hasOne(GiataGeography::class, 'city_id', 'city_id');
    }

    public function hbsi(): HasOne
    {
        return $this->hasOne(Mapping::class, 'giata_id', 'code')
            ->hBSI()
            ->connection(config('database.connections.mysql_cache'));
    }
}
