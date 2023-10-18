<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GiataProperty extends Model
{
    use HasFactory;

    protected $connection;

    protected $fillable = [
        'code',
        'last_updated',
        'name',
        'chain',
        'city',
        'locale',
        'address',
        'phone',
        'position',
        'url',
        'cross_references',
    ];

    protected $casts = [
        'chain' => 'json',
        'address' => 'json',
        'phone' => 'json',
        'position' => 'json',
        'cross_references' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'giata_properties';
    }

    public function mapperExpediaGiata(): HasOne
    {
        return $this->hasOne(MapperExpediaGiata::class, 'giata_code', 'code');
    }
}
