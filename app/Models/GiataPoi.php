<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataPoi extends Model
{
    use HasFactory;

    protected $fillable = [
        'poi_id',
        'name_primary',
        'type',
        'country_code',
        'lat',
        'lon',
        'places',
        'name_others',
    ];

    protected $casts = [
        'places' => 'json',
        'name_others' => 'json',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('SUPPLIER_CONTENT_DB_CONNECTION'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . 'giata_pois';
    }
}
