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

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('SUPPLIER_CONTENT_DB_CONNECTION'), 'mysql2');
        $this->table = env(('SUPPLIER_CONTENT_DB_DATABASE'), 'ujv_api') . '.' . 'mapper_hbsi_giatas';
    }
}
