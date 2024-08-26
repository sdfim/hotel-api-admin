<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapperHbsiGiata extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'hbsi_id',
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
    protected $primaryKey = ['hbsi_id', 'giata_id'];

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'mapper_hbsi_giatas';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $mainDB = config('database.connections.mysql.database');

        $this->table = "$mainDB.mapper_hbsi_giatas";
        $this->connection = config('database.active_connections.mysql');
    }
}
