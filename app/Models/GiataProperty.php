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
	protected $primaryKey = 'code';
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
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_properties';
    }

    /**
     * @return HasOne
     */
    public function mapperExpediaGiata(): HasOne
    {
        return $this->hasOne(MapperExpediaGiata::class, 'giata_code', 'code');
    }

	/**
     * @return HasOne
     */
    public function giataGeography(): HasOne
    {
        return $this->hasOne(GiataGeography::class, 'city_id', 'city_id');
    }
}
