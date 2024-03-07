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

    /**
     * @var string[]
     */
    protected $casts = [
        'chain' => 'json',
        'address' => 'json',
        'phone' => 'json',
        'position' => 'json',
        'cross_references' => 'json',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('SUPPLIER_CONTENT_DB_CONNECTION'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . 'giata_properties';
    }

    /**
     * @return HasOne
     */
    public function mapperExpediaGiata(): HasOne
    {
        return $this->hasOne(MapperExpediaGiata::class, 'giata_id', 'code');
    }

    /**
     * @return HasOne
     */
    public function giataGeography(): HasOne
    {
        return $this->hasOne(GiataGeography::class, 'city_id', 'city_id');
    }

    /**
     * @return HasOne
     */
    public function hbsi(): HasOne
    {
        return $this->hasOne(MapperHbsiGiata::class, 'giata_id', 'code');
    }
}
