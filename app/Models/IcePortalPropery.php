<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IcePortalPropery extends Model
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

    protected $table = 'ice_hbsi_properties';

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
            'images' => 'json',
            'amenities' => 'json',
        ];
    }

    public function mapperHbsiGiata(): HasMany
    {
        return $this->hasMany(MapperIcePortalGiata::class, 'ice_portal_id', 'code');
    }
}
