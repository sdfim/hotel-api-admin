<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataGeography extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;

    protected $fillable = [
        'city_id',
        'city_name',
        'locale_id',
        'locale_name',
        'country_code',
        'country_name',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('SUPPLIER_CONTENT_DB_CONNECTION'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . 'giata_geographies';
    }
}
