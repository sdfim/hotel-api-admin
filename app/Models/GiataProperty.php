<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GiataProperty extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * @var bool
     */
    public $incrementing = false;

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
        'phone',
        'position',
        'latitude',
        'longitude',
        'url',
        'cross_references',
    ];

    protected $table = 'giata_properties';

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
            'chain' => 'json',
            'address' => 'json',
            'phone' => 'json',
            'position' => 'json',
            'cross_references' => 'json',
        ];
    }

    public function mapperExpediaGiata(): HasOne
    {
        return $this->hasOne(MapperExpediaGiata::class, 'giata_id', 'code');
    }

    public function giataGeography(): HasOne
    {
        return $this->hasOne(GiataGeography::class, 'city_id', 'city_id');
    }

    public function hbsi(): HasOne
    {
        return $this->hasOne(MapperHbsiGiata::class, 'giata_id', 'code')
            ->connection(config('database.connections.mysql_cache'));
    }
}
