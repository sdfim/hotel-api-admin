<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MapperExpediaGiata extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'expedia_id',
        'giata_id',
        'step',
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

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'mapper_expedia_giatas';
    }

    /**
     * @return HasOne
     */
    public function expedia(): HasOne
    {
        return $this->hasOne(ExpediaContent::class, 'property_id', 'expedia_id');
    }
}
