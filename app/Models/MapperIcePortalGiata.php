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

    /**
     * @var string[]
     */
    protected $primaryKey = ['ice_portal_id', 'giata_id'];

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'mapper_ice_portal_giatas';
}
