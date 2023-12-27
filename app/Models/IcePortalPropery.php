<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcePortalPropery extends Model
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
        'supplier_id',
        'name',
        'city',
        'state',
        'country',
        'addressLine1',
        'phone',
        'latitude',
        'longitude',
        'images',
        'amenities',
        'editDate',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'images' => 'json',
        'amenities' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api').'.'.'ice_hbsi_properties';
    }

    public function mapperHbsiGiata()
    {
        return $this->hasMany(MapperIcePortalGiata::class, 'ice_portal_id', 'code');
    }
}
