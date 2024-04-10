<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataPlace extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'parent_key',
        'name_primary',
        'type',
        'state',
        'country_code',
        'name_others',
        'tticodes',
    ];

    protected $casts = [
        'airports' => 'array',
        'name_others' => 'array',
        'tticodes' => 'array',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('SUPPLIER_CONTENT_DB_CONNECTION'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . 'giata_places';
    }
}
