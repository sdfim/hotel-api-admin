<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapperIcePortalGiata extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'ice_portal_id',
        'giata_id',
        'perc',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var mixed
     */
    protected $connection;

    protected $primaryKey = ['expedia_id', 'giata_id'];

    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api').'.'.'mapper_ice_portal_giatas';
    }
}
